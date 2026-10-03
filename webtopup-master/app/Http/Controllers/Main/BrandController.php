<?php

namespace App\Http\Controllers\Main;

use App\Enums\CacheGroupEnum;
use App\Http\Controllers\Controller;
use App\Models\PPOB\PPOBBrand;
use App\Models\Web\Faq;
use App\Services\SakurupiahService;
use App\Traits\WithVersionedCache;
use Inertia\Response;

class BrandController extends Controller
{
    use WithVersionedCache;

    public function show(PPOBBrand $brand): Response
    {
        $brand = $this->flexibleVersioned(CacheGroupEnum::BRANDS, "detail:{$brand->slug}", [1800, 3600], function () use ($brand) {
            $brand->load(['products.media', 'category']);

            $brand->image = $brand->getFirstMediaUrl('image');
            $brand->banner = $brand->getFirstMediaUrl('banner');
            $brand->default_product_image = $brand->getFirstMediaUrl('default_product_image');

            $brand->products->each(function ($product) use ($brand) {
                $product->image = $product->getFirstMediaUrl('image') ?: $brand->default_product_image;
                $product->makeHidden('media');
            });

            $brand->makeHidden('media');

            return $brand;
        });

        $faqs = $this->flexibleVersioned(CacheGroupEnum::FAQS, 'active', [3600, 7200], function () {
            return Faq::where('status', true)->orderBy('order', 'asc')->get();
        });

        $settingTitle = getSetting('title');
        $settingFavicon = getSetting('favicon') ?: '/favicon.svg';

        $sakurupiahMethods = app(SakurupiahService::class)->paymentMethods();

        $activePrices = $brand->products->where('status', true)->pluck('sell_price');
        $brandUrl = route('product.show', $brand->slug);
        $brandImage = $brand->image ?: (config('app.url').$settingFavicon);

        return inertia()->render('main/BrandDetail', [
            'brand' => $brand,
            'faqs' => $faqs,
            'payment_methods' => $sakurupiahMethods,
        ])->withViewData([
            'meta' => [
                'title' => "{$brand->name} - Top Up Murah & Cepat | {$settingTitle}",
                'description' => "Top up {$brand->name} termurah dan terpercaya di {$settingTitle}. Proses instan, tersedia berbagai metode pembayaran.",
                'keywords' => "top up {$brand->name}, beli {$brand->name}, harga {$brand->name}, {$brand->name} murah, {$settingTitle}, topup game",
                'author' => $settingTitle,
                'application_name' => $settingTitle,
                'url' => $brandUrl,
                'image' => $brandImage,
                'structured_data' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $brand->name,
                        'description' => $brand->description ?: "Top up {$brand->name} termurah dan terpercaya di {$settingTitle}.",
                        'image' => $brandImage,
                        'url' => $brandUrl,
                        'brand' => [
                            '@type' => 'Brand',
                            'name' => $brand->name,
                        ],
                        'offers' => [
                            '@type' => 'AggregateOffer',
                            'priceCurrency' => 'IDR',
                            'lowPrice' => (float) ($activePrices->min() ?? 0),
                            'highPrice' => (float) ($activePrices->max() ?? 0),
                            'offerCount' => $activePrices->count(),
                            'availability' => 'https://schema.org/InStock',
                            'url' => $brandUrl,
                        ],
                    ],
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => [
                            [
                                '@type' => 'ListItem',
                                'position' => 1,
                                'name' => 'Home',
                                'item' => route('home'),
                            ],
                            [
                                '@type' => 'ListItem',
                                'position' => 2,
                                'name' => $brand->category?->name ?? 'Produk',
                                'item' => route('home', ['category' => $brand->category?->slug]),
                            ],
                            [
                                '@type' => 'ListItem',
                                'position' => 3,
                                'name' => $brand->name,
                                'item' => $brandUrl,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
