<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SakurupiahService
{
    public function __construct(
        public readonly ?string $apiId = null,
        public readonly ?string $apiKey = null,
        public readonly ?bool $isProduction = null,
        public readonly ?string $baseUrl = null,
    ) {}

    public function createInvoice(
        string $method,
        string $merchantReference,
        int $amount,
        string $name,
        string $email,
        string $phone,
        string $callbackUrl,
        string $returnUrl,
        string $productName,
        string $productPrice,
    ): array {
        $apiId = $this->apiId ?? config('sakurupiah.api_id');
        $apiKey = $this->apiKey ?? config('sakurupiah.api_key');
        $amountString = (string) $amount;
        $method = trim($method);

        $payload = [
            'api_id' => $apiId,
            'method' => $method,
            'name' => $name,
            'email' => $email,
            'phone' => $this->normalizePhone($phone),
            'amount' => $amount,
            'merchant_fee' => config('sakurupiah.merchant_fee', 1),
            'merchant_ref' => $merchantReference,
            'expired' => 24,
            'produk' => [$productName],
            'qty' => [1],
            'harga' => [$productPrice],
            'size' => [''],
            'note' => [''],
            'callback_url' => $callbackUrl,
            'return_url' => $returnUrl,
            'signature' => hash_hmac('sha256', $apiId.$method.$merchantReference.$amountString, $apiKey),
        ];

        $response = Http::withToken($apiKey)
            ->asForm()
            ->acceptJson()
            ->timeout(30)
            ->post($this->endpoint('create.php'), $payload);

        return $this->normalizeInvoiceResponse($response);
    }

    public function validateCallbackSignature(string $rawPayload, string $signature): bool
    {
        $apiKey = $this->apiKey ?? config('sakurupiah.api_key');

        return hash_equals(hash_hmac('sha256', $rawPayload, $apiKey), $signature);
    }

    /**
     * Return the payment methods currently supported by Sakurupiah.
     *
     * @return array<int, array{id: string, name: string, fee: float|int, action: 'multiply'|'add', img: string}>
     */
    public function paymentMethods(): array
    {
        $response = Http::withToken($this->apiKey ?? config('sakurupiah.api_key'))
            ->asForm()
            ->acceptJson()
            ->timeout(15)
            ->connectTimeout(10)
            ->post($this->endpoint('list-payment.php'), [
                'api_id' => $this->apiId ?? config('sakurupiah.api_id'),
                'method' => 'list',
            ]);

        if (! $response->successful() || (string) $response->json('status') !== '200') {
            return [];
        }

        return collect($response->json('data', []))
            ->filter(fn (mixed $method): bool => is_array($method) && ($method['status'] ?? '') === 'Aktif')
            ->map(function (array $method): array {
                $fee = (float) ($method['biaya'] ?? 0);
                $isPercent = strtolower((string) ($method['percent'] ?? '')) === 'percent';

                return [
                    'id' => (string) ($method['kode'] ?? ''),
                    'name' => (string) ($method['nama'] ?? $method['kode'] ?? ''),
                    'fee' => $isPercent ? $fee / 100 : $fee,
                    'action' => $isPercent ? 'multiply' : 'add',
                    'img' => (string) ($method['logo'] ?? ''),
                    'minimum' => (int) ($method['minimal'] ?? 0),
                    'maximum' => (int) ($method['maksimal'] ?? 0),
                    'type' => (string) ($method['tipe'] ?? ''),
                ];
            })
            ->filter(fn (array $method): bool => $method['id'] !== '')
            ->values()
            ->all();
    }

    private function endpoint(string $path): string
    {
        $baseUrl = rtrim($this->baseUrl ?? config('sakurupiah.base_url'), '/');
        $prefix = ($this->isProduction ?? config('sakurupiah.is_production', true))
            ? 'api'
            : 'api-sanbox';

        return "{$baseUrl}/{$prefix}/{$path}";
    }

    private function normalizeInvoiceResponse(Response $response): array
    {
        $body = $response->json();
        $invoice = $body['data'][0] ?? [];
        $successful = $response->successful() && (string) ($body['status'] ?? '') === '200';

        return [
            'successful' => $successful,
            'transaction_id' => $invoice['trx_id'] ?? null,
            'account' => $invoice['payment_no'] ?? ($invoice['qr'] ?? null),
            'code' => $invoice['payment_kode'] ?? null,
            'redirect_url' => $invoice['checkout_url'] ?? null,
            'expired_at' => $invoice['expired'] ?? null,
            'message' => $body['message'] ?? $response->reason(),
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\\D+/', '', $phone) ?: '';

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}
