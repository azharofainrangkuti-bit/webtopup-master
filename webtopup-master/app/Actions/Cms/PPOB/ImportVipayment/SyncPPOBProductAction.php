<?php

namespace App\Actions\Cms\PPOB\ImportVipayment;

use App\Enums\CacheGroupEnum;
use App\Models\PPOB\PPOBBrand;
use App\Models\PPOB\PPOBCategory;
use App\Models\PPOB\PPOBProduct;
use App\Services\VipaymentService;
use App\Traits\WithVersionedCache;
use Triyatna\PhpValidGame\ValidGameClient;

class SyncPPOBProductAction
{
    use WithVersionedCache;

    public function __construct(
        public readonly VipaymentService $vipaymentService,
        public readonly ValidGameClient $validGameClient,
    ) {}

    public function handle(): void
    {
        $products = $this->vipaymentService->getProductList();

        PPOBProduct::query()
            ->where('provider', 'vipayment')
            ->delete();

        PPOBBrand::query()
            ->where('provider', 'vipayment')
            ->get()
            ->each
            ->delete();

        $categoryMap = [];
        $brandMap = [];
        $maxOrder = PPOBBrand::max('order') ?? 0;

        foreach ($products as $productData) {
            $catName = $productData['category'];
            if (! isset($categoryMap[$catName])) {
                $category = PPOBCategory::firstOrCreate(
                    ['name' => $catName],
                    ['description' => 'Imported from ViPayment', 'status' => true],
                );
                $categoryMap[$catName] = $category->id;
            }
            $catId = $categoryMap[$catName];

            $brandName = $productData['brand'];
            $brandKey = $catId.'_'.$brandName;
            if (! isset($brandMap[$brandKey])) {
                $brand = PPOBBrand::firstOrCreate(
                    [
                        'p_p_o_b_category_id' => $catId,
                        'name' => $brandName,
                    ],
                    [
                        'provider' => 'vipayment',
                        'description' => 'Imported from ViPayment',
                        'featured' => false,
                        'order' => ++$maxOrder,
                        'settings' => $this->settingsForBrand($catName, $brandName),
                        'status' => true,
                    ],
                );
                $brandMap[$brandKey] = $brand->id;
            }
            $brandId = $brandMap[$brandKey];

            PPOBProduct::updateOrCreate(
                ['p_p_o_b_brand_id' => $brandId, 'sku' => $productData['sku']],
                [
                    'provider' => 'vipayment',
                    'vipayment_endpoint' => $productData['endpoint'],
                    'name' => $productData['name'],
                    'description' => 'Imported from ViPayment',
                    'buy_price' => $productData['price'],
                    'sell_price' => (int) ceil($productData['price'] * 1.1),
                    'status' => $productData['status'],
                ],
            );
        }

        $this->flushCacheGroup(CacheGroupEnum::BRANDS, CacheGroupEnum::CATEGORIES);
    }

    private function settingsForBrand(string $category, string $brandName): array
    {
        $name = strtolower($brandName);

        // "Via Login" products are topped up with the account's own email/username
        // and password, not a numeric ID, so they need their own input type.
        if (str_contains($name, 'via login')) {
            return [
                'type' => 'login',
                'label_id' => 'Email / Username',
                'label_server' => 'Password',
                'instructions' => 'Nonaktifkan verifikasi 2 langkah (2FA/A2F) dan logout dari perangkat lain sebelum melakukan top up, karena akun akan diakses langsung oleh admin/sistem.',
            ];
        }

        if ($category === 'Token PLN') {
            return [
                'type' => 'id',
                'label_id' => 'Nomor Meter / ID Pelanggan',
            ];
        }

        if ($category === 'Saldo E-money') {
            return [
                'type' => 'id',
                'label_id' => 'Nomor HP / ID Akun',
            ];
        }

        if ($category !== 'Game') {
            return [
                'type' => 'id',
                'label_id' => 'Nomor Tujuan / ID Pelanggan',
            ];
        }

        $registeredGame = collect($this->validGameClient->listGames())
            ->first(function (array $game) use ($name): bool {
                $label = strtolower((string) ($game['label'] ?? ''));

                return $label !== '' && ($name === $label || str_contains($name, $label) || str_contains($label, $name));
            });

        if ($registeredGame !== null && ($registeredGame['requiresZone'] ?? false)) {
            return [
                'type' => 'id+server',
                'label_id' => 'User ID',
                'label_server' => 'Server / Zone ID',
            ];
        }

        $serverRequiredGames = preg_match('/afk journey|ace racer|age of empires|arena breakout|arena of valor|astral guardians|be the king|blockman go|blood strike|castle duels|chimeraland|delta force|dragon raja|dragonheir|dynasty heroes|dynasty warriors|eggy party|farlight 84|football master|genshin impact|honkai|honor of kings|identity v|king of avalon|lifeafter|light of thel|lords mobile|love and deepspace|magic chess|marvel duel|marvel rivals|marvel snap|marvel super war|metal slug|nba infinite|omega legends|one punch man|point blank|ragnarok|revelation infinite|rush royale|state of survival|super sus|tank company|tarisland|tom and jerry|tower of fantasy|war robots|watcher of realms|whiteout survival|zenless zone zero/', $name);

        if ($serverRequiredGames) {
            return [
                'type' => 'id+server',
                'label_id' => 'User ID',
                'label_server' => 'Server / Zone ID',
            ];
        }

        return [
            'type' => 'id',
            'label_id' => 'User ID',
        ];
    }
}
