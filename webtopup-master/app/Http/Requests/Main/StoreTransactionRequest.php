<?php

namespace App\Http\Requests\Main;

use App\Models\PPOB\PPOBProduct;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Triyatna\PhpValidGame\Laravel\Facades\ValidGame;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'nullable|in:id,id+server,login',
            'account_id' => 'required_if:type,id',
            'server_id' => 'required_if:type,id+server',
            'product_id' => 'required|exists:p_p_o_b_products,id',
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric',
            'payment_type' => 'required|in:automatic,manual',
            'payment_method' => 'required|string|max:255', // in:qris,bca,bni,bri,mandiri,permata
            'voucher_code' => 'nullable|string|exists:vouchers,code',
        ];
    }

    /**
     * Enforce the input mode stored on the selected ViPayment brand.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $product = PPOBProduct::with('brand')->find($this->input('product_id'));

            if (! $product || $product->provider !== 'vipayment') {
                return;
            }

            $expectedType = $product->brand?->settings['type'] ?? 'id';
            if ($this->input('type') !== $expectedType) {
                $validator->errors()->add('type', 'Input type does not match this product.');
            }

            if (in_array($expectedType, ['id+server', 'login'], true) && blank($this->input('server_id'))) {
                $label = $expectedType === 'login' ? 'Password' : 'Server / Zone ID';
                $validator->errors()->add('server_id', "{$label} is required for this product.");
            }
        });
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $product = PPOBProduct::find($this->product_id);

        // Pro Version
        // Validate the account only for games supported by the nickname checker.
        $brandName = strtolower($product->brand->name);
        $game = Str::contains($brandName, 'mobile legend')
            ? 'mobilelegends'
            : (Str::contains($brandName, 'free fire') ? 'freefire' : null);

        if ($game === 'mobilelegends') {
            $resolve = ValidGame::check(
                game: $game,
                userId: $this->account_id,
                zoneId: $this->server_id,
            );

            if (! $resolve->isValid()) {
                throw ValidationException::withMessages([
                    'account_id' => $resolve->getMessage() ?: 'Game ID atau server tidak valid',
                ]);
            }
        }

        // Merge additional data
        $this->merge([
            'p_p_o_b_brand_id' => $product->p_p_o_b_brand_id,
            'p_p_o_b_product_id' => $product->id,
            'submited' => [
                'account_id' => $this->account_id,
                'server_id' => $this->server_id ?? null,
            ],
        ]);
    }
}
