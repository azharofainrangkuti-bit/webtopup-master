<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class VipaymentService
{
    public function __construct(
        public readonly ?string $apiId = null,
        public readonly ?string $apiKey = null,
        public readonly ?string $baseUrl = null,
    ) {}

    /**
     * @return array<int, array{category: string, brand: string, sku: string, name: string, price: int, status: bool, endpoint: string}>
     */
    public function getProductList(): array
    {
        $prepaidResponse = $this->request('api/prepaid', [
            'type' => 'services',
        ]);

        $gameResponse = $this->request('api/game-feature', [
            'type' => 'services',
        ]);

        foreach ([$prepaidResponse, $gameResponse] as $response) {
            if (! $response->successful() || $response->json('result') !== true || ! is_array($response->json('data'))) {
                $message = (string) $response->json('message', '');

                if (str_contains(strtolower($message), 'not permitted')) {
                    throw new \RuntimeException(
                        "ViPayment rejected this request: {$message}. ".
                        'Whitelist this IP in the ViPayment dashboard, or set VIPAYMENT_HTTP_PROXY '.
                        'to route requests through one fixed IP so you only whitelist it once.'
                    );
                }

                throw new \RuntimeException('ViPayment catalog endpoint did not return a valid service list.');
            }
        }

        $prepaidItems = collect($prepaidResponse->json('data', []))
            ->filter(fn (mixed $item): bool => is_array($item))
            ->map(fn (array $item): array => [
                'category' => $this->mapCategory($item),
                'brand' => (string) ($item['brand'] ?? $item['category'] ?? 'ViPayment'),
                'sku' => (string) ($item['code'] ?? ''),
                'name' => (string) ($item['name'] ?? ''),
                'price' => (int) ($item['price']['basic'] ?? 0),
                'status' => ($item['status'] ?? null) === 'available',
                'endpoint' => 'api/prepaid',
            ]);

        $gameItems = collect($gameResponse->json('data', []))
            ->filter(fn (mixed $item): bool => is_array($item))
            ->map(fn (array $item): array => [
                'category' => $this->semanticCategory($item) ?? 'Game',
                'brand' => (string) ($item['game'] ?? 'Games'),
                'sku' => (string) ($item['code'] ?? ''),
                'name' => (string) ($item['name'] ?? ''),
                'price' => (int) ($item['price']['basic'] ?? 0),
                'status' => ($item['status'] ?? null) === 'available',
                'endpoint' => 'api/game-feature',
            ]);

        return $prepaidItems
            ->concat($gameItems)
            ->filter(fn (array $item): bool => $item['sku'] !== '' && $item['name'] !== '' && $item['price'] > 0)
            ->sortBy(fn (array $item): string => sprintf('%d-%012d', $item['status'] ? 0 : 1, $item['price']))
            ->unique('sku')
            ->unique(fn (array $item): string => strtolower(implode('|', [
                $item['category'],
                $item['brand'],
                $item['name'],
            ])))
            ->values()
            ->all();
    }

    private function mapCategory(array $item): string
    {
        $type = strtolower((string) ($item['type'] ?? ''));
        $semanticCategory = $this->semanticCategory($item);

        if ($semanticCategory !== null) {
            return $semanticCategory;
        }

        if ($type === 'paket-lainnya' && preg_match('/voucher|digital voucher|carrefour|transmart|yoshinoya/', $this->searchableProductText($item))) {
            return 'Voucher';
        }

        return match ($type) {
            'paket-internet' => 'Paket Internet',
            'paket-lainnya' => 'App Premium',
            'streaming-tv' => 'Streaming & TV',
            'paket-telepon' => 'Paket SMS/Telepon',
            'pulsa-reguler' => 'Pulsa Reguler',
            'pulsa-transfer' => 'Pulsa Transfer',
            'saldo-emoney', 'saldo-e-money' => 'Saldo E-money',
            'pulsa-internasional' => 'Pulsa Internasional',
            'voucher-game' => 'Voucher',
            'social-media', 'sosial-media' => 'App Premium',
            'token-pln' => 'Token PLN',
            default => 'App Premium',
        };
    }

    private function semanticCategory(array $item): ?string
    {
        $brand = strtolower((string) ($item['brand'] ?? $item['game'] ?? ''));
        $product = strtolower((string) ($item['name'] ?? ''));
        $searchable = $this->searchableProductText($item);
        $isGameFeature = array_key_exists('game', $item);

        if (preg_match('/wifi\s*id|wifi\.id/', $brand.' '.$product)) {
            return 'Paket Internet';
        }

        if (preg_match('/voucher|gift card|wallet code|garena shell|google play|apple gift|playstation network|\bpsn\b|steam wallet/', $isGameFeature ? $brand : $searchable)) {
            return 'Voucher';
        }

        if (preg_match('/netflix|amazon prime|disney|vidio|vision plus|\bviu\b|wetv|iqiyi|rcti|bstation|youtube\s*premium/', $isGameFeature ? $brand : $searchable)) {
            return 'Streaming & TV';
        }

        if (preg_match('/alight motion|canva|capcut|chatgpt|gemini|getcontact|perplexity|picsart|resso|spotify|telegram premium|tiktok music|microsoft\s*365|office\s*365|zoom\s*premium|warp plus/', $isGameFeature ? $brand : $searchable)) {
            return 'App Premium';
        }

        if ($isGameFeature) {
            return null;
        }

        return null;
    }

    private function searchableProductText(array $item): string
    {
        return strtolower(implode(' ', [
            (string) ($item['brand'] ?? $item['game'] ?? ''),
            (string) ($item['category'] ?? ''),
            (string) ($item['name'] ?? ''),
        ]));
    }

    /**
     * @return array{successful: bool, status: string, transaction_id: ?string, serial_number: ?string, message: string, payload: array<string, mixed>}
     */
    public function createTransaction(string $productCode, string $destination, ?string $serverId = null, string $endpoint = 'api/game-feature'): array
    {
        $payload = [
            'type' => 'order',
            'service' => $productCode,
            'data_no' => $destination,
        ];

        if ($serverId !== null && $serverId !== '') {
            $payload['zone_id'] = $serverId;
        }

        $response = $this->request($endpoint, $payload);
        $result = $this->normalizeResponse($response);

        if (! $result['successful'] && $endpoint === 'api/game-feature' && str_contains(strtolower($result['message']), 'layanan tidak ditemukan')) {
            unset($payload['zone_id']);
            $response = $this->request('api/prepaid', $payload);
            $result = $this->normalizeResponse($response);
        }

        return $result;
    }

    /**
     * @return array{successful: bool, status: string, transaction_id: ?string, serial_number: ?string, message: string, payload: array<string, mixed>}
     */
    public function checkStatus(string $transactionId): array
    {
        $payload = [
            'type' => 'status',
            'trxid' => $transactionId,
        ];

        $response = $this->request('api/prepaid', $payload);
        $result = $this->normalizeResponse($response);

        if (! $result['successful'] || empty($result['transaction_id'])) {
            $responseGame = $this->request('api/game-feature', $payload);
            $resultGame = $this->normalizeResponse($responseGame);
            if ($resultGame['successful']) {
                $result = $resultGame;
            }
        }

        return $result;
    }

    public function validateWebhookSignature(string $signature): bool
    {
        return hash_equals($this->signature(), $signature);
    }

    private function request(string $path, array $payload): Response
    {
        $proxy = config('vipayment.http_proxy');

        return Http::acceptJson()
            ->asForm()
            ->timeout(30)
            ->connectTimeout(10)
            ->when($proxy, fn ($http) => $http->withOptions(['proxy' => $proxy]))
            ->post($this->endpoint($path), [
                'key' => $this->apiKey(),
                'sign' => $this->signature(),
                ...$payload,
            ]);
    }

    private function endpoint(string $path = 'api/prepaid'): string
    {
        $path = ltrim($path, '/');

        return rtrim($this->baseUrl ?? config('vipayment.base_url'), '/').'/'.$path;
    }

    private function signature(): string
    {
        return md5($this->apiId().$this->apiKey());
    }

    private function apiId(): string
    {
        return (string) ($this->apiId ?? config('vipayment.api_id'));
    }

    private function apiKey(): string
    {
        return (string) ($this->apiKey ?? config('vipayment.api_key'));
    }

    /**
     * @return array{successful: bool, status: string, transaction_id: ?string, serial_number: ?string, message: string, payload: array<string, mixed>}
     */
    private function normalizeResponse(Response $response): array
    {
        $body = $response->json() ?? [];
        $data = $body['data'] ?? [];
        $transaction = is_array($data) && array_is_list($data) ? ($data[0] ?? []) : $data;
        $status = strtolower((string) ($transaction['status'] ?? ''));

        return [
            'successful' => $response->successful() && ($body['result'] ?? false) === true,
            'status' => $status,
            'transaction_id' => $transaction['trxid']
                ?? $transaction['trx_id']
                ?? $transaction['transaction_id']
                ?? null,
            'serial_number' => $transaction['note'] ?? $transaction['sn'] ?? null,
            'message' => (string) ($body['message'] ?? $response->reason()),
            'payload' => $body,
        ];
    }
}
