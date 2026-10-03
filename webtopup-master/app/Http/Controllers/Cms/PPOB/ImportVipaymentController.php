<?php

namespace App\Http\Controllers\Cms\PPOB;

use App\Actions\Cms\PPOB\ImportVipayment\SyncPPOBProductAction;
use App\Http\Controllers\Controller;
use App\Models\PPOB\PPOBProduct;
use App\Services\VipaymentService;
use Illuminate\Support\Facades\Gate;

class ImportVipaymentController extends Controller
{
    protected string $resource = PPOBProduct::class;

    public function __construct(public readonly VipaymentService $vipaymentService) {}

    public function index()
    {
        Gate::authorize('view'.$this->resource);

        $products = collect($this->vipaymentService->getProductList())
            ->groupBy('category')
            ->map(fn ($items, $category): array => [
                'name' => $category,
                'brands' => $items->groupBy('brand')
                    ->map(fn ($brandItems, $brand): array => [
                        'name' => $brand,
                        'products' => $brandItems->values(),
                    ])->values(),
            ])->values();

        return inertia('cms/ppob/import-vipayment/Index', ['products' => $products]);
    }

    public function sync(SyncPPOBProductAction $action)
    {
        Gate::authorize('create'.$this->resource);

        $action->handle();

        return back();
    }
}
