<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Banner extends Model
{
    use \App\Traits\HasCustomAsset;

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
        ];
    }

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

    public function scopeFlashSale(Builder $query): Builder
    {
        return $this->scopeActive($query)->where('is_flash_sale', true);
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) return '';
        $path = $this->image;
        // The image is stored in public/banner/. Pass it through the trait
        // which handles the /public/ prefix when the script is mounted in a
        // sub-folder.
        return self::getCustomAssetUrl($path);
    }
}
