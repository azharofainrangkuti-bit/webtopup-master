<?php

namespace App\Actions\Api\V1\Callback;

use App\Enums\PaymentStatusEnum;
use App\Mail\PaymentFailed;
use App\Mail\PaymentSuccess;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use App\Services\SakurupiahService;
use App\Services\VipaymentService;
use Illuminate\Support\Facades\Mail;
use Triyatna\Digiflazz\Digiflazz;

class HandleSakurupiahCallbackAction
{
    public function __construct(
        public readonly SakurupiahService $sakurupiahService,
        public readonly VipaymentService $vipaymentService,
    ) {}

    public function handle(string $rawPayload, string $signature, string $event): Payment
    {
        if (! $this->sakurupiahService->validateCallbackSignature($rawPayload, $signature)) {
            throw new \Exception('Invalid Sakurupiah callback signature', 403);
        }

        if ($event !== 'payment_status') {
            throw new \Exception('Unrecognized Sakurupiah callback event', 400);
        }

        $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        $payment = Payment::where('order_id', $payload['merchant_ref'] ?? '')->first();

        if (! $payment) {
            throw new \Exception('Transaction not found', 404);
        }

        $statusCode = (int) ($payload['status_kode'] ?? 0);

        $payment->update([
            'transaction_id' => $payload['trx_id'] ?? $payment->transaction_id,
            'paid_at' => $statusCode === 1 ? now() : $payment->paid_at,
            'expired_at' => $statusCode === 2 ? now() : $payment->expired_at,
        ]);

        if (($payload['status'] ?? null) === 'berhasil' && $statusCode === 1) {
            $this->markPaid($payment);
        } elseif (($payload['status'] ?? null) === 'expired' && $statusCode === 2) {
            $this->markFailed($payment);
        }

        return $payment->fresh();
    }

    private function markPaid(Payment $payment): void
    {
        if ($payment->payable_type !== Order::class) {
            return;
        }

        $order = $payment->payable;
        $order->update(['payment_status' => PaymentStatusEnum::SETTLEMENT]);

        if ($order->brand?->provider === 'digiflazz') {
            Digiflazz::createPrepaidTransaction(
                productCode: $order->product->sku,
                customerNo: ($order->submited['account_id'] ?? '').($order->submited['server_id'] ?? ''),
                refId: $order->reference,
            );
        } elseif ($order->brand?->provider === 'vipayment') {
            $transaction = $this->vipaymentService->createTransaction(
                productCode: $order->product->sku,
                destination: (string) ($order->submited['account_id'] ?? ''),
                serverId: $order->submited['server_id'] ?? null,
                endpoint: $order->product->vipayment_endpoint ?? 'api/game-feature',
            );

            if ($transaction['transaction_id']) {
                $payment->update(['transaction_id' => $transaction['transaction_id']]);
            }
        }

        $this->sendNotification($order, true);
    }

    private function markFailed(Payment $payment): void
    {
        if ($payment->payable_type !== Order::class) {
            return;
        }

        $order = $payment->payable;
        $order->update(['payment_status' => PaymentStatusEnum::DENY]);
        $this->sendNotification($order, false);
    }

    private function sendNotification(Order $order, bool $success): void
    {
        $template = getSetting($success ? 'template_payment_confirmation' : 'template_payment_rejected');
        $message = str_replace(
            ['{customer_name}', '{order_id}', '{app_name}', '{link}', '{cs_link}'],
            [$order->name, $order->reference, config('app.name'), route('transaction.show', ['order' => $order]), getSetting('cs')],
            $template,
        );

        Mail::to($order->email)->send($success ? new PaymentSuccess($order) : new PaymentFailed($order));
        $order->notifications()->create([
            'provider' => 'email',
            'title' => 'Payment '.($success ? 'Confirmed' : 'Rejected'),
            'content' => $message,
            'error' => false,
        ]);
    }
}
