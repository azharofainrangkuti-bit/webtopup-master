<?php

use App\Actions\Api\V1\Callback\HandleSakurupiahCallbackAction;
use App\Actions\Api\V1\Callback\HandleVipaymentCallbackAction;
use App\Http\Controllers\Api\V1\Callback\DigiflazzController;
use App\Http\Controllers\Api\V1\Callback\MidtransController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Callback URL for Midtrans
Route::post('/midtrans/callback', [MidtransController::class, 'callback'])->name('midtrans.callback');

Route::post('/sakurupiah/callback', function (Request $request, HandleSakurupiahCallbackAction $action) {
    $action->handle(
        rawPayload: $request->getContent(),
        signature: (string) $request->header('X-Callback-Signature'),
        event: (string) $request->header('X-Callback-Event'),
    );

    return response()->json(['success' => true, 'message' => 'Payment status received']);
})->name('sakurupiah.callback');

Route::post('/vipayment/callback', function (Request $request, HandleVipaymentCallbackAction $action) {
    $action->handle(
        payload: $request->all(),
        signature: (string) $request->header('X-Client-Signature'),
    );

    return response()->json(['success' => true, 'message' => 'ViPayment report received']);
})->name('vipayment.callback');

// Callback URL for Digiflazz
Route::post('/digiflazz/callback', [DigiflazzController::class, 'callback'])->name('digiflazz.callback');
