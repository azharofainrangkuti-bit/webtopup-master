<?php

namespace App\Actions\Main;

use App\Models\PPOB\PPOBBrand;
use App\Models\PPOB\PPOBProduct;
use Illuminate\Support\Str;
use Triyatna\PhpValidGame\Laravel\Facades\ValidGame;

class CheckGameAccountAction
{
    /**
     * Handle the action.
     */
    public function handle(array $data): array
    {
        $brandName = '';

        if (isset($data['product_id'])) {
            $product = PPOBProduct::find($data['product_id']);
            $brandName = $product->brand->name;
        } elseif (isset($data['slug'])) {
            $brand = PPOBBrand::where('slug', $data['slug'])->firstOrFail();
            $brandName = $brand->name;
        }

        $game = $this->supportedGame($brandName);

        if ($game === null) {
            return [
                'status' => false,
                'message' => 'Game validation not supported for this product',
            ];
        }

        $result = ValidGame::check(
            game: $game,
            userId: $data['account_id'],
            zoneId: $data['server_id'] ?? null,
        );

        $isValid = $result->isValid();

        return [
            // ViPayment accepts Free Fire data_no directly; nickname providers
            // can reject valid accounts by region.
            'status' => $isValid || $game === 'freefire',
            'message' => $isValid
                ? $result->getMessage()
                : ($game === 'freefire' ? 'ID diterima, nickname belum dapat diverifikasi.' : $result->getMessage()),
            'verification_pending' => ! $isValid && $game === 'freefire',
            'data' => [
                'username' => $result->nickname,
                'nickname' => $result->nickname,
                'game' => $result->game,
            ],
        ];
    }

    private function supportedGame(string $brandName): ?string
    {
        $name = Str::lower($brandName);

        return match (true) {
            Str::contains($name, 'mobile legend') => 'mobilelegends',
            Str::contains($name, 'free fire') => 'freefire',
            default => null,
        };
    }
}
