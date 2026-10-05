<?php

namespace App\Models;

use App\Traits\HasCustomAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Banner extends Model
{
    use HasCustomAsset;
    use SoftDeletes;

    protected $fillable = [
        'image',
        'primary_text',
        'tagline',
        'show_button',
        'button_name',
        'button_link',
        'sort_order',
        'is_active',
        'start_date',
        'end_date',
        'is_flash_sale',
    ];

    protected function casts(): array
    {
        return [
            'show_button' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_flash_sale' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Clear all cached storefront banner lists whenever any banner changes.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('home_banners_list');
            Cache::forget('home_flash_sale_banner');
        });

        static::deleted(function () {
            Cache::forget('home_banners_list');
            Cache::forget('home_flash_sale_banner');
        });

        static::restored(function () {
            Cache::forget('home_banners_list');
            Cache::forget('home_flash_sale_banner');
        });

        static::forceDeleted(function () {
            Cache::forget('home_banners_list');
            Cache::forget('home_flash_sale_banner');
        });
    }

    /**
     * Scope for strictly active and scheduled banners on the storefront.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
    }

    /**
     * Scope for active flash sale banners.
     */
    public function scopeFlashSale(Builder $query): Builder
    {
        return $query->active()->where('is_flash_sale', true);
    }

    public function getImageUrlAttribute(): string
    {
        $placeholder = asset('assets/images/placeholder.svg');
        if (!$this->image) return $placeholder;
        return self::resolveMediaUrl($this->image, $placeholder) ?: $placeholder;
    }
}
