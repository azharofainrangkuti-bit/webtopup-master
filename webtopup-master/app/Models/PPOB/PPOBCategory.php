<?php

namespace App\Models\PPOB;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PPOBCategory extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia;

    /**
     * The only categories the storefront recognizes, in display order.
     * Any other category is leftover/unmapped data and should not surface publicly.
     */
    public const STOREFRONT_CATEGORIES = [
        'Game',
        'App Premium',
        'Saldo E-money',
        'Voucher',
        'Paket Internet',
        'Paket SMS/Telepon',
        'Pulsa Transfer',
        'Pulsa Reguler',
        'Pulsa Internasional',
        'Streaming & TV',
        'Token PLN',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Raw SQL CASE expression that orders rows by STOREFRONT_CATEGORIES position.
     */
    public static function storefrontOrderSql(): string
    {
        $cases = [];
        foreach (self::STOREFRONT_CATEGORIES as $index => $name) {
            $escaped = str_replace("'", "''", $name);
            $cases[] = "WHEN '{$escaped}' THEN ".($index + 1);
        }

        return 'CASE name '.implode(' ', $cases).' ELSE 99 END';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom([
                'name',
            ])
            ->saveSlugsTo('slug')
            ->usingSuffixGenerator(
                fn (string $slug, int $iteration) => bin2hex(random_bytes(4))
            );
    }

    public function brands()
    {
        return $this->hasMany(PPOBBrand::class, 'p_p_o_b_category_id');
    }

    public function activeBrands()
    {
        return $this->hasMany(PPOBBrand::class, 'p_p_o_b_category_id')->where('status', true);
    }
}
