<?php

use App\Services\VipaymentService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('maps available ViPayment services into PPOB products', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => [[
                'brand' => 'Telkomsel',
                'code' => 'T10',
                'name' => 'Pulsa 10k',
                'price' => ['basic' => 10000],
                'status' => 'available',
                'category' => 'Pulsa',
                'type' => 'pulsa-reguler',
            ]],
        ]),
        'vip-reseller.co.id/api/game-feature' => Http::response([
            'result' => true,
            'data' => [[
                'game' => 'Free Fire',
                'code' => 'FF50',
                'name' => '50 Diamonds',
                'price' => ['basic' => 6000],
                'status' => 'available',
            ]],
        ]),
    ]);

    $products = (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList();

    expect($products)->toBe([
        [
            'category' => 'Game',
            'brand' => 'Free Fire',
            'sku' => 'FF50',
            'name' => '50 Diamonds',
            'price' => 6000,
            'status' => true,
            'endpoint' => 'api/game-feature',
        ],
        [
            'category' => 'Pulsa Reguler',
            'brand' => 'Telkomsel',
            'sku' => 'T10',
            'name' => 'Pulsa 10k',
            'price' => 10000,
            'status' => true,
            'endpoint' => 'api/prepaid',
        ],
    ]);
});

it('maps streaming services to the Streaming & TV category', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => [[
                'brand' => 'K-VISION DAN GOL',
                'code' => 'NETFLIX1',
                'name' => 'K-Vision Paket Anak 30 Hari',
                'price' => ['basic' => 10000],
                'status' => 'available',
                'type' => 'streaming-tv',
            ]],
        ]),
        'vip-reseller.co.id/api/game-feature' => Http::response([
            'result' => true,
            'data' => [],
        ]),
    ]);

    expect((new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList()[0]['category'])
        ->toBe('Streaming & TV');
});

it('maps digital vouchers listed under paket lainnya to Voucher', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => [[
                'brand' => 'CARREFOUR / TRANSMART',
                'code' => 'CARREFOUR100',
                'name' => 'Digital Voucher Carrefour Transmart 100K',
                'price' => ['basic' => 100000],
                'status' => 'available',
                'type' => 'paket-lainnya',
            ]],
        ]),
        'vip-reseller.co.id/api/game-feature' => Http::response([
            'result' => true,
            'data' => [],
        ]),
    ]);

    expect((new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList()[0]['category'])
        ->toBe('Voucher');
});

it('overrides endpoint defaults for premium apps and wifi id', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => [[
                'brand' => 'WIFI ID',
                'code' => 'WIFI1',
                'name' => 'Akses Wifi.id 1 Hari',
                'price' => ['basic' => 5000],
                'status' => 'available',
                'type' => 'paket-lainnya',
            ]],
        ]),
        'vip-reseller.co.id/api/game-feature' => Http::response([
            'result' => true,
            'data' => [[
                'game' => 'Canva Pro',
                'code' => 'CANVA1',
                'name' => 'Canvaa Pro 1 Bulan',
                'price' => ['basic' => 15000],
                'status' => 'available',
            ]],
        ]),
    ]);

    $categories = collect((new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList())
        ->mapWithKeys(fn ($product) => [$product['brand'] => $product['category']]);

    expect($categories['WIFI ID'])->toBe('Paket Internet')
        ->and($categories['Canva Pro'])->toBe('App Premium');
});

it('keeps game brands in Game even when a product name contains voucher text', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => [],
        ]),
        'vip-reseller.co.id/api/game-feature' => Http::response([
            'result' => true,
            'data' => [[
                'game' => 'Roblox Via Login',
                'code' => 'ROBLOX1',
                'name' => 'Voucher Robux 80',
                'price' => ['basic' => 10000],
                'status' => 'available',
            ], [
                'game' => 'Arena of Valor',
                'code' => 'AOV1',
                'name' => 'Voucher AOV 100',
                'price' => ['basic' => 10000],
                'status' => 'available',
            ]],
        ]),
    ]);

    expect(collect((new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList())
        ->pluck('category')->unique()->all())->toBe(['Game']);
});

it('submits a ViPayment order with the documented request fields', function () {
    Http::fake([
        'vip-reseller.co.id/api/game-feature' => Http::response([
            'result' => true,
            'data' => ['trxid' => 'VP123', 'status' => 'waiting', 'note' => ''],
            'message' => 'Pesanan berhasil, pesanan akan diproses.',
        ]),
    ]);

    $transaction = (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))
        ->createTransaction('FF50', '12345678');

    expect($transaction['successful'])->toBeTrue()
        ->and($transaction['transaction_id'])->toBe('VP123')
        ->and($transaction['status'])->toBe('waiting');

    Http::assertSent(fn (Request $request): bool => $request->data()['type'] === 'order'
        && $request->data()['service'] === 'FF50'
        && $request->data()['data_no'] === '12345678');
});

it('submits prepaid products to the prepaid endpoint', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => ['trxid' => 'VP-PREPAID', 'status' => 'waiting'],
        ]),
    ]);

    (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))
        ->createTransaction('T10', '08123456789', endpoint: 'api/prepaid');

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/api/prepaid')
        && $request->data()['service'] === 'T10');
});

it('sends the ViPayment zone separately from the account ID', function () {
    Http::fake([
        'vip-reseller.co.id/api/game-feature' => Http::response([
            'result' => true,
            'data' => ['trxid' => 'VP456', 'status' => 'waiting', 'note' => ''],
        ]),
    ]);

    (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))
        ->createTransaction('MLA5-S13', '12345678', '2052');

    Http::assertSent(fn (Request $request): bool => $request->data()['data_no'] === '12345678'
        && $request->data()['zone_id'] === '2052'
        && ! str_contains($request->data()['data_no'], '2052'));
});

it('normalizes alternate ViPayment transaction fields', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => ['trx_id' => 'VP789', 'status' => 'successful', 'sn' => 'SN123'],
        ]),
    ]);

    $transaction = (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))
        ->checkStatus('VP789');

    expect($transaction['transaction_id'])->toBe('VP789')
        ->and($transaction['serial_number'])->toBe('SN123')
        ->and($transaction['status'])->toBe('successful');
});

it('keeps one valid product when ViPayment repeats a SKU', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => [[
                'brand' => 'Provider',
                'code' => 'DUPLICATE',
                'name' => 'First product',
                'price' => ['basic' => 1000],
                'status' => 'available',
                'category' => 'Pulsa',
                'type' => 'pulsa-reguler',
            ], [
                'brand' => 'Provider',
                'code' => 'DUPLICATE',
                'name' => 'Repeated product',
                'price' => ['basic' => 1000],
                'status' => 'available',
                'category' => 'Pulsa',
                'type' => 'pulsa-reguler',
            ]],
        ]),
        'vip-reseller.co.id/api/game-feature' => Http::response(['result' => true, 'data' => []]),
    ]);

    $products = (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList();

    expect($products)->toHaveCount(1)
        ->and($products[0]['name'])->toBe('First product');
});

it('keeps the cheapest variant when visible product names repeat', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => [[
                'brand' => 'Telkomsel',
                'code' => 'EXPENSIVE',
                'name' => 'Internet 10GB',
                'price' => ['basic' => 12000],
                'status' => 'available',
                'category' => 'Umum',
                'type' => 'paket-internet',
            ], [
                'brand' => 'Telkomsel',
                'code' => 'CHEAP',
                'name' => 'Internet 10GB',
                'price' => ['basic' => 10000],
                'status' => 'available',
                'category' => 'Umum',
                'type' => 'paket-internet',
            ]],
        ]),
        'vip-reseller.co.id/api/game-feature' => Http::response(['result' => true, 'data' => []]),
    ]);

    $products = (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList();

    expect($products)->toHaveCount(1)
        ->and($products[0]['sku'])->toBe('CHEAP')
        ->and($products[0]['price'])->toBe(10000);
});

it('prefers an available variant over a cheaper unavailable duplicate', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response([
            'result' => true,
            'data' => [[
                'brand' => 'Telkomsel',
                'code' => 'UNAVAILABLE',
                'name' => 'Internet 10GB',
                'price' => ['basic' => 9000],
                'status' => 'empty',
                'category' => 'Umum',
                'type' => 'paket-internet',
            ], [
                'brand' => 'Telkomsel',
                'code' => 'AVAILABLE',
                'name' => 'Internet 10GB',
                'price' => ['basic' => 10000],
                'status' => 'available',
                'category' => 'Umum',
                'type' => 'paket-internet',
            ]],
        ]),
        'vip-reseller.co.id/api/game-feature' => Http::response(['result' => true, 'data' => []]),
    ]);

    $products = (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList();

    expect($products)->toHaveCount(1)
        ->and($products[0]['sku'])->toBe('AVAILABLE')
        ->and($products[0]['status'])->toBeTrue();
});

it('rejects an incomplete ViPayment catalog response', function () {
    Http::fake([
        'vip-reseller.co.id/api/prepaid' => Http::response(['result' => false, 'data' => []], 200),
        'vip-reseller.co.id/api/game-feature' => Http::response(['result' => true, 'data' => []]),
    ]);

    expect(fn () => (new VipaymentService('api-id', 'api-key', 'https://vip-reseller.co.id'))->getProductList())
        ->toThrow(RuntimeException::class);
});
