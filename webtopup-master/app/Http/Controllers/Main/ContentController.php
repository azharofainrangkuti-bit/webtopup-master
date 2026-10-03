<?php

namespace App\Http\Controllers\Main;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\PPOB\PPOBCategory;
use App\Models\Voucher\Voucher;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Display the privacy policy page.
     */
    public function privacyPolicy()
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';

        return inertia()->render('main/PrivacyPolicy')->withViewData([
            'meta' => [
                'title' => 'Kebijakan Privasi - '.$settingTitle,
                'description' => 'Kebijakan Privasi '.$settingTitle.'. Pelajari bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda saat menggunakan layanan kami.',
                'keywords' => 'kebijakan privasi, privacy policy, '.$settingTitle.', perlindungan data, keamanan informasi',
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('privacy-policy'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }

    /**
     * Display the terms and conditions page.
     */
    public function terms()
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';

        return inertia()->render('main/Terms')->withViewData([
            'meta' => [
                'title' => 'Syarat & Ketentuan - '.$settingTitle,
                'description' => 'Syarat & Ketentuan '.$settingTitle.'. Baca ketentuan penggunaan layanan kami untuk pengalaman transaksi yang aman dan nyaman.',
                'keywords' => 'syarat dan ketentuan, terms and conditions, '.$settingTitle.', aturan penggunaan, perjanjian pengguna',
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('terms'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }

    /**
     * Display the price list page.
     */
    public function priceList(Request $request)
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';

        $categories = PPOBCategory::query()
            ->with(['activeBrands.products' => function ($query) {
                $query->where('status', true)->orderBy('sell_price');
            }])
            ->where('status', true)
            ->whereIn('name', PPOBCategory::STOREFRONT_CATEGORIES)
            ->orderByRaw(PPOBCategory::storefrontOrderSql())
            ->get()
            ->map(function ($category) {
                $category->image = $category->getFirstMediaUrl('image');
                $category->activeBrands->each(function ($brand) {
                    $brand->image = $brand->getFirstMediaUrl('image');
                    $brand->makeHidden('media');
                });
                $category->makeHidden('media');

                return $category;
            });

        return inertia()->render('main/PriceList', [
            'categories' => $categories,
        ])->withViewData([
            'meta' => [
                'title' => 'Daftar Harga & Price List - '.$settingTitle,
                'description' => 'Cek daftar harga produk topup game termurah dan terupdate di '.$settingTitle.'.',
                'keywords' => 'daftar harga topup, price list game, topup murah, '.$settingTitle,
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('price-list'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }

    /**
     * Display customer reviews & ratings page.
     */
    public function reviews()
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';

        $recentOrders = Order::with('brand', 'product')
            ->where('payment_status', 2)
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($order) {
                $maskedName = preg_replace('/.(?=.{2})/', '*', $order->name);

                return [
                    'id' => $order->id,
                    'name' => $maskedName ?: 'Pengguna',
                    'brand_name' => $order->brand->name ?? 'Game',
                    'product_name' => $order->product->name ?? 'Item',
                    'rating' => 5,
                    'comment' => 'Proses cepat dan aman banget! Recomended topup di sini.',
                    'created_at' => $order->created_at->diffForHumans(),
                ];
            });

        return inertia()->render('main/Reviews', [
            'reviews' => $recentOrders,
        ])->withViewData([
            'meta' => [
                'title' => 'Ulasan & Testimoni Pelanggan - '.$settingTitle,
                'description' => 'Lihat ulasan dan kepuasan ribuan pelanggan yang sudah bertransaksi di '.$settingTitle.'.',
                'keywords' => 'ulasan pelanggan, testimoni topup, reputasi, '.$settingTitle,
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('reviews'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }

    /**
     * Display currently active public vouchers and promotions.
     */
    public function promo()
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';
        $now = now();

        $vouchers = Voucher::query()
            ->where('status', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->where(function ($query) {
                $query->where('usage_limit', 0)->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->latest('created_at')
            ->get()
            ->map(fn (Voucher $voucher): array => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
                'description' => $voucher->description,
                'type' => $voucher->type,
                'fixed_amount' => $voucher->fixed_amount,
                'percentage' => $voucher->percentage,
                'start_date' => $voucher->start_date?->toISOString(),
                'end_date' => $voucher->end_date?->toISOString(),
                'min_purchase_amount' => $voucher->min_purchase_amount,
                'usage_limit' => $voucher->usage_limit,
                'used_count' => $voucher->used_count,
            ]);

        return inertia()->render('main/Promo', [
            'vouchers' => $vouchers,
        ])->withViewData([
            'meta' => [
                'title' => 'Promo Top Up Game - '.$settingTitle,
                'description' => 'Temukan promo dan voucher top up game aktif di '.$settingTitle.'.',
                'keywords' => 'promo topup game, voucher game, diskon topup, '.$settingTitle,
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('promo'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }

    /**
     * Display the reseller partnership entry point.
     */
    public function reseller()
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';
        $rawPhone = preg_replace('/\D/', '', (string) getSetting('phone'));
        $phone = str_starts_with($rawPhone, '0') ? '62'.substr($rawPhone, 1) : $rawPhone;

        return inertia()->render('main/Reseller', [
            'support_url' => $phone ? 'https://wa.me/'.$phone : '#',
        ])->withViewData([
            'meta' => [
                'title' => 'Program Reseller - '.$settingTitle,
                'description' => 'Bergabung menjadi reseller top up game dan layani pelanggan dengan proses cepat dari '.$settingTitle.'.',
                'keywords' => 'reseller topup game, partner topup, affiliate game, '.$settingTitle,
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('reseller'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }

    /**
     * Display the game calculator page.
     */
    public function calculator()
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';

        return inertia()->render('main/Calculator')->withViewData([
            'meta' => [
                'title' => 'Kalkulator Win Rate & Magic Wheel MLBB - '.$settingTitle,
                'description' => 'Hitung target Win Rate Mobile Legends dan estimasi Diamond Magic Wheel dengan akurat di '.$settingTitle.'.',
                'keywords' => 'kalkulator win rate mlbb, kalkulator wr ml, magic wheel calculator, topup ml, '.$settingTitle,
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('calculator'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }

    /**
     * Display the public API documentation entry point.
     */
    public function docs()
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';

        return inertia()->render('main/Docs')->withViewData([
            'meta' => [
                'title' => 'Dokumentasi API - '.$settingTitle,
                'description' => 'Dokumentasi integrasi API top up game untuk partner dan reseller '.$settingTitle.'.',
                'keywords' => 'dokumentasi api, api topup game, reseller, '.$settingTitle,
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('docs'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }

    /**
     * Display a privacy-preserving activity leaderboard.
     */
    public function leaderboard()
    {
        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';
        $leaders = Order::query()
            ->select('name')
            ->where('payment_status', PaymentStatusEnum::SETTLEMENT)
            ->whereNotNull('name')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Order $order, int $index): array => [
                'rank' => $index + 1,
                'name' => preg_replace('/.(?=.{2})/', '*', $order->name) ?: 'Pengguna',
                'badge' => $index < 3 ? ['🥇', '🥈', '🥉'][$index] : '🎮',
            ]);

        return inertia()->render('main/Leaderboard', [
            'leaders' => $leaders,
        ])->withViewData([
            'meta' => [
                'title' => 'Leaderboard Gamer - '.$settingTitle,
                'description' => 'Aktivitas gamer terbaru di '.$settingTitle.' dengan identitas pengguna yang tetap terlindungi.',
                'keywords' => 'leaderboard gamer, topup game, '.$settingTitle,
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => route('leaderboard'),
                'image' => config('app.url').$settingFavicon,
            ],
        ]);
    }
}
