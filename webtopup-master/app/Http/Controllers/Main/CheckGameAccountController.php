<?php

namespace App\Http\Controllers\Main;

use App\Actions\Main\CheckGameAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Main\CheckGameAccountRequest;

class CheckGameAccountController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function check(CheckGameAccountRequest $request, CheckGameAccountAction $action)
    {
        $result = $action->handle($request->validated());

        if (! $result['status']) {
            return $this->responseWithError(
                $result['message'] ?? 'Game ID tidak dapat diverifikasi oleh provider',
                422,
            );
        }

        return $this->responseWithSuccess([
            ...($result['data'] ?? []),
            'verification_pending' => $result['verification_pending'] ?? false,
            'message' => $result['message'] ?? null,
        ], $result['message'] ?? 'Game ID berhasil diverifikasi');
    }
}
