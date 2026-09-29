<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image', 'sort_order', 'is_primary'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'sort_order' => 'integer'];
    }

    use \App\Traits\HasCustomAsset;

    public function product() { return $this->belongsTo(Product::class); }

    public function getUrlAttribute(): string
    {
        if (!$this->image) return '';
        $path = $this->image;
        if (!str_starts_with($path, 'product/') && !str_starts_with($path, 'category/') && !str_starts_with($path, 'settings/')) {
            $path = 'storage/' . $path;
        }
        return self::getCustomAssetUrl($path);
    }
}