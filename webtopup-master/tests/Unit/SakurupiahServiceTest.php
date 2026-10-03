<?php

use App\Actions\Main\StoreTransactionAction;
use App\Services\MidtransService;
use App\Services\SakurupiahService;
use App\Services\VodaService;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('returns the supported payment methods configured for Sakurupiah', function () {
    Http::fake([
        'sakurupiah.id/api/list-payment.php' => Http::response([
            'status' => '200',
            'data' => [
                [
                    'kode' => 'QRIS',
                    'nama' => 'QRIS',
                    'biaya' => '0.7',
                    'percent' => 'Percent',
                    'logo' => 'https://sakurupiah.id/assets/img/payment/QRIS.png',
                    'status' => 'Aktif',
                ],
                [
                    'kode' => 'OFFLINE',
                    'nama' => 'Offline',
                    'status' => 'Tidak Aktif',
                ],
            ],
        ]),
    ]);

    $methods = (new SakurupiahService)->paymentMethods();

    expect($methods)->toHaveCount(1)
        ->and($methods[0]['id'])->toBe('QRIS')
        ->and($methods[0]['name'])->toBe('QRIS')
        ->and(round($methods[0]['fee'], 3))->toBe(0.007)
        ->and($methods[0]['action'])->toBe('multiply')
        ->and($methods[0]['img'])->toContain('/payment/QRIS.png');

    Http::assertSent(fn (Request $request): bool => $request->data()['api_id'] !== null
        && $request->data()['method'] === 'list'
        && isset($request->headers()['Authorization']));
});

it('sends exact provider method codes for mixed-case payment methods', function () {
    Http::fake([
        'sakurupiah.id/api/create.php' => Http::response([
            'status' => '200',
            'data' => [[
                'trx_id' => 'trx_123',
                'payment_no' => '123456',
                'checkout_url' => 'https://sakurupiah.id/invoice-payout.php?reference_id=trx_123',
                'expired' => '2026-09-14 10:00:00',
            ]],
            'message' => 'success',
        ]),
    ]);

    (new SakurupiahService)->createInvoice(
        method: 'ShopeePay',
        merchantReference: 'order-123',
        amount: 100000,
        name: 'Test User',
        email: 'test@example.com',
        phone: '081234567890',
        callbackUrl: 'https://example.com/callback',
        returnUrl: 'https://example.com/return',
        productName: 'Test Product',
        productPrice: '100000',
    );

    Http::assertSent(fn (Request $request): bool => $request->data()['method'] === 'ShopeePay'
        && $request->data()['merchant_ref'] === 'order-123');
});

it('uses the active request host for Sakurupiah callback URLs instead of localhost', function () {
    config()->set('app.url', 'https://localhost');
    app()->instance('request', HttpRequest::create('https://checkout.example.com/checkout', 'POST'));

    $action = new StoreTransactionAction(
        new MidtransService,
        new SakurupiahService,
        new VodaService,
    );

    expect($action->sakurupiahCallbackUrl())->toBe('https://checkout.example.com/api/v1/sakurupiah/callback');
});
