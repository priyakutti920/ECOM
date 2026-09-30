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
        if (file_exists(public_path($path))) {
            return self::getCustomAssetUrl($path);
        }
        if (!str_starts_with($path, 'storage/')) {
            $path = 'storage/' . $path;
        }
        return self::getCustomAssetUrl($path);
    }
}