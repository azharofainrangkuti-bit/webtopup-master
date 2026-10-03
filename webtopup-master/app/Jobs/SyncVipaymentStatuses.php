<?php

namespace App\Jobs;

use App\Enums\DigiflazzStatusEnum;
use App\Mail\TopupSuccess;
use App\Models\Order\Order;
use App\Services\VipaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SyncVipaymentStatuses implements ShouldQueue
{
    use Queueable;

    public function handle(VipaymentService $vipaymentService): void
    {
        Order::query()
            ->whereHas('brand', fn ($query) => $query->where('provider', 'vipayment'))
            ->whereHas('payment', fn ($query) => $query->whereNotNull('transaction_id'))
            ->whereIn('topup_status', [
                DigiflazzStatusEnum::PENDING,
                DigiflazzStatusEnum::ON_PROGRESS,
            ])
            ->with('payment')
            ->chunkById(100, function ($orders) use ($vipaymentService): void {
                foreach ($orders as $order) {
                    $result = $vipaymentService->checkStatus($order->payment->transaction_id);
                    $status = strtolower($result['status']);

                    if (in_array($status, ['success', 'successful', 'berhasil', 'sukses'], true)) {
                        if ($order->topup_status === DigiflazzStatusEnum::SUCCESS) {
                            continue;
                        }

                        $order->update([
                            'sn' => $result['serial_number'],
                            'topup_status' => DigiflazzStatusEnum::SUCCESS,
                        ]);
                        Mail::to($order->email)->send(new TopupSuccess($order->fresh()));
                        $order->notifications()->create([
                            'provider' => 'email',
                            'title' => 'Order Completed',
                            'content' => getSetting('template_order_completed'),
                            'error' => false,
                        ]);
                    } elseif (in_array($status, ['error', 'failed', 'failure', 'gagal'], true)) {
                        $order->update(['topup_status' => DigiflazzStatusEnum::FAILED]);
                    } elseif ($status !== '') {
                        $order->update(['topup_status' => DigiflazzStatusEnum::ON_PROGRESS]);
                    }
                }
            });
    }
}
