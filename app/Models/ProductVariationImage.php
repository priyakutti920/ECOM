<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariationImage extends Model
{
    protected $table = 'product_variation_images';

    protected $fillable = ['product_variation_id', 'image', 'sort_order', 'is_primary'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'sort_order' => 'integer'];
    }

    use \App\Traits\HasCustomAsset;

    public function variation() { return $this->belongsTo(ProductVariation::class, 'product_variation_id'); }

    public function getUrlAttribute(): string
    {
        if (!$this->image) return '';
        return self::resolveMediaUrl($this->image) ?? '';
    }
}