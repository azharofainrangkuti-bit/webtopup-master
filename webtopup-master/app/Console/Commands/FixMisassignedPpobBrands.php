<?php

namespace App\Console\Commands;

use App\Enums\CacheGroupEnum;
use App\Models\PPOB\PPOBBrand;
use App\Models\PPOB\PPOBCategory;
use App\Traits\WithVersionedCache;
use Illuminate\Console\Command;

class FixMisassignedPpobBrands extends Command
{
    use WithVersionedCache;

    protected $signature = 'ppob:fix-brand-categories {--force : Actually apply the changes instead of a dry run}';

    protected $description = 'Move specific brands found in the wrong PPOB category into their correct category, and disable known test/dummy products';

    /**
     * Brand id => correct category name, found by auditing brand/product names
     * against their assigned category.
     */
    private const REASSIGNMENTS = [
        5193 => 'Game',        // ROBLOX (Robux top-up) was under App Premium
        5019 => 'Voucher',     // RAZER GOLD wallet credit was under App Premium
        5158 => 'Voucher',     // ANCOL theme park e-ticket was under App Premium
        5194 => 'Voucher',     // JUNGLELAND theme park e-ticket was under App Premium
        5107 => 'App Premium', // "Generate QR Upi" utility was under Game
        5081 => 'App Premium', // LIKEE (social app top-up) was under Voucher
        5085 => 'App Premium', // Likee (social app top-up) was under Game
    ];

    /**
     * Brand ids that are provider test/dummy data and should never be sold.
     */
    private const DISABLE_BRAND_IDS = [4965]; // SMART -> only product is "DUMMY"

    public function handle(): int
    {
        $categoryIds = PPOBCategory::whereIn('name', array_unique(array_values(self::REASSIGNMENTS)))
            ->pluck('id', 'name');

        foreach (self::REASSIGNMENTS as $brandId => $categoryName) {
            $brand = PPOBBrand::with('category')->find($brandId);

            if (! $brand) {
                $this->warn("Brand #{$brandId} not found, skipping.");

                continue;
            }

            $targetCategoryId = $categoryIds[$categoryName] ?? null;

            if (! $targetCategoryId) {
                $this->error("Target category \"{$categoryName}\" not found, skipping brand #{$brandId}.");

                continue;
            }

            $currentCategoryName = $brand->category->name ?? 'NULL';
            $this->line("#{$brandId} \"{$brand->name}\": {$currentCategoryName} -> {$categoryName}");

            if ($this->option('force')) {
                $brand->update(['p_p_o_b_category_id' => $targetCategoryId]);
            }
        }

        foreach (self::DISABLE_BRAND_IDS as $brandId) {
            $brand = PPOBBrand::with('products')->find($brandId);

            if (! $brand) {
                continue;
            }

            $this->line("#{$brandId} \"{$brand->name}\": disabling brand and its products (test/dummy data)");

            if ($this->option('force')) {
                $brand->update(['status' => false]);
                $brand->products()->update(['status' => false]);
            }
        }

        if (! $this->option('force')) {
            $this->comment('Dry run only. Re-run with --force to apply these changes.');
        } else {
            $this->flushCacheGroup(CacheGroupEnum::BRANDS, CacheGroupEnum::CATEGORIES);
            $this->info('Applied all brand category fixes and disabled test/dummy products.');
        }

        return self::SUCCESS;
    }
}
