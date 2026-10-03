<?php

namespace App\Actions\Api\V1\Callback;

use App\Enums\DigiflazzStatusEnum;
use App\Mail\TopupSuccess;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use App\Services\VipaymentService;
use Illuminate\Support\Facades\Mail;

class HandleVipaymentCallbackAction
{
    public function __construct(public readonly VipaymentService $vipaymentService) {}

    public function handle(array $payload, string $signature): Order
    {
        if (! $this->vipaymentService->validateWebhookSignature($signature)) {
            throw new \Exception('Invalid ViPayment webhook signature', 403);
        }

        $transaction = $payload['data'] ?? $payload;
        $transaction = is_array($transaction) && array_is_list($transaction) ? ($transaction[0] ?? []) : $transaction;
        $transactionId = (string) (
            $transaction['trxid']
            ?? $transaction['trx_id']
            ?? $transaction['transaction_id']
            ?? ''
        );

        $payment = Payment::where('transaction_id', $transactionId)->firstOrFail();
        $order = $payment->payable;

        if (! $order instanceof Order) {
            throw new \Exception('Order not found', 404);
        }

        $status = strtolower((string) ($transaction['status'] ?? ''));

        if (in_array($status, ['success', 'successful', 'berhasil', 'sukses'], true)) {
            $this->markSuccessful($order, $transaction);
        } elseif (in_array($status, ['error', 'failed', 'failure', 'gagal'], true)) {
            $order->update(['topup_status' => DigiflazzStatusEnum::FAILED]);
        } else {
            $order->update(['topup_status' => DigiflazzStatusEnum::ON_PROGRESS]);
        }

        return $order->fresh();
    }

    private function markSuccessful(Order $order, array $transaction): void
    {
        if ($order->topup_status === DigiflazzStatusEnum::SUCCESS) {
            return;
        }

        $order->update([
            'sn' => $transaction['note'] ?? $transaction['sn'] ?? null,
            'topup_status' => DigiflazzStatusEnum::SUCCESS,
        ]);

        Mail::to($order->email)->send(new TopupSuccess($order));
        $order->notifications()->create([
            'provider' => 'email',
            'title' => 'Order Completed',
            'content' => getSetting('template_order_completed'),
            'error' => false,
        ]);
    }
}
