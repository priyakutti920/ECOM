<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'image', 'sort_order', 'parent_id', 'status',
        'is_active', 'meta_title', 'meta_description', 'meta_keywords'
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'parent_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // status 0 = not deleted, is_active = 1 means visible
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 0)->where('is_active', true);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->where('status', 0);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    use \App\Traits\HasCustomAsset;

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) return '';
        $path = $this->image;
        if (!str_starts_with($path, 'product/') && !str_starts_with($path, 'category/') && !str_starts_with($path, 'settings/')) {
            $path = 'storage/' . $path;
        }
        return self::getCustomAssetUrl($path);
    }
}
