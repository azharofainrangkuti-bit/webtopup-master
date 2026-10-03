<?php

namespace App\Console\Commands;

use App\Models\PPOB\PPOBCategory;
use Illuminate\Console\Command;

class CleanupPpobCategories extends Command
{
    protected $signature = 'ppob:categories-cleanup {--force : Actually delete the categories instead of a dry run}';

    protected $description = 'Remove leftover PPOB categories outside the storefront whitelist (only if they have no brands attached)';

    public function handle(): int
    {
        $junk = PPOBCategory::query()
            ->withCount('brands')
            ->whereNotIn('name', PPOBCategory::STOREFRONT_CATEGORIES)
            ->get();

        $deletable = $junk->where('brands_count', 0);
        $blocked = $junk->where('brands_count', '>', 0);

        if ($blocked->isNotEmpty()) {
            $this->warn('Skipping categories that still have brands attached (reassign their brands first):');
            foreach ($blocked as $category) {
                $this->line("  #{$category->id} \"{$category->name}\" - {$category->brands_count} brand(s)");
            }
        }

        if ($deletable->isEmpty()) {
            $this->info('No orphaned categories to remove.');

            return self::SUCCESS;
        }

        $this->info("Found {$deletable->count()} orphaned category/categories with no brands:");
        foreach ($deletable as $category) {
            $this->line("  #{$category->id} \"{$category->name}\"");
        }

        if (! $this->option('force')) {
            $this->comment('Dry run only. Re-run with --force to delete these categories.');

            return self::SUCCESS;
        }

        foreach ($deletable as $category) {
            $category->delete();
        }

        $this->info("Deleted {$deletable->count()} orphaned category/categories.");

        return self::SUCCESS;
    }
}
