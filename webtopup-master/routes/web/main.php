<?php

use App\Http\Controllers\Main\BrandController;
use App\Http\Controllers\Main\CheckGameAccountController;
use App\Http\Controllers\Main\ContentController;
use App\Http\Controllers\Main\HomeController;
use App\Http\Controllers\Main\PasswordController;
use App\Http\Controllers\Main\ProfileController;
use App\Http\Controllers\Main\QuickSearchController;
use App\Http\Controllers\Main\TransactionController;
use App\Models\PPOB\PPOBBrand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/top-up', fn () => redirect()->to(route('home').'#produk-unggulan'))->name('top-up');
Route::get('/promo', [ContentController::class, 'promo'])->name('promo');
Route::get('/reseller', [ContentController::class, 'reseller'])->name('reseller');
Route::get('/sitemap.xml', function () {
    $urls = collect([
        route('home'),
        route('price-list'),
        route('reviews'),
        route('calculator'),
        route('docs'),
        route('leaderboard'),
        route('privacy-policy'),
        route('terms'),
    ])->map(fn (string $url) => ['loc' => $url]);

    $brandUrls = PPOBBrand::query()
        ->where('status', true)
        ->pluck('slug')
        ->map(fn (string $slug) => ['loc' => route('product.show', $slug)]);

    return Response::view('sitemap', ['urls' => $urls->concat($brandUrls)])
        ->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::get('/search/quick', QuickSearchController::class)->name('search.quick');
Route::get('/privacy-policy', [ContentController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms', [ContentController::class, 'terms'])->name('terms');
Route::get('/price-list', [ContentController::class, 'priceList'])->name('price-list');
Route::get('/reviews', [ContentController::class, 'reviews'])->name('reviews');
Route::get('/calculator', [ContentController::class, 'calculator'])->name('calculator');
Route::get('/docs', [ContentController::class, 'docs'])->name('docs');
Route::get('/leaderboard', [ContentController::class, 'leaderboard'])->name('leaderboard');
Route::get('/id-id/docs', fn () => to_route('docs'))->name('localized.docs');
Route::get('/id-id/invoices/{order}', fn (string $order) => to_route('transaction.show', ['order' => $order]))->name('localized.invoice');
Route::get('/invoices/{order}', fn (string $order) => to_route('transaction.show', ['order' => $order]))->name('invoice.show');
Route::get('/id-id/price-list', fn () => to_route('price-list'))->name('localized.price-list');
Route::get('/id-id/reviews', fn () => to_route('reviews'))->name('localized.reviews');
Route::get('/id-id/privacy-policy', fn () => to_route('privacy-policy'))->name('localized.privacy-policy');
Route::get('/id-id/terms-and-condition', fn () => to_route('terms'))->name('localized.terms');
Route::get('/brand/{brand}', [BrandController::class, 'show'])->name('product.show');

Route::post('/checkout', [TransactionController::class, 'store'])->name('checkout.store');
Route::get('/transaction/{order}', [TransactionController::class, 'show'])->name('transaction.show');
Route::get('/transaction', [TransactionController::class, 'check'])->name('transaction.check');
Route::put('/transaction/{order}', [TransactionController::class, 'update'])->name('transaction.update');
Route::post('/check-game-account', [CheckGameAccountController::class, 'check'])->name('check-game-account');
Route::post('/check-voucher', [TransactionController::class, 'checkVoucher'])->name('check-voucher');

Route::get('/profile', [ProfileController::class, 'index'])->name('main.profile.index')->middleware('auth');
Route::patch('/profile', [ProfileController::class, 'update'])->name('main.profile.update')->middleware('auth');
Route::patch('/password', [PasswordController::class, 'update'])->name('main.password.update')->middleware('auth');

// After login
Route::get('/after-login', function () {
    if (auth()->user()->hasRole(['superadmin', 'admin'])) {
        return to_route('cms.dashboard');
    } else {
        return to_route('home');
    }
})->name('after-login')->middleware('auth');
