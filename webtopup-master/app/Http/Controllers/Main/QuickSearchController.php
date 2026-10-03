<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\PPOB\PPOBBrand;
use Illuminate\Http\Request;

class QuickSearchController extends Controller
{
    /**
     * Lightweight public search used by the global search modal.
     * Returns popular brands when the query is empty.
     */
    public function __invoke(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = PPOBBrand::query()
            ->with('category:id,name')
            ->where('status', true)
            ->select(['id', 'p_p_o_b_category_id', 'name', 'slug', 'featured', 'order']);

        if ($term !== '') {
            $query->where('name', 'like', "%{$term}%");
        } else {
            $query->where('featured', true);
        }

        $brands = $query
            ->orderByRaw('CASE WHEN featured = 1 THEN 0 ELSE 1 END')
            ->orderBy('order')
            ->limit(8)
            ->get()
            ->map(fn (PPOBBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'category' => $brand->category?->name,
                'image' => $brand->getFirstMediaUrl('image'),
            ]);

        return response()->json([
            'query' => $term,
            'results' => $brands,
        ]);
    }
}
