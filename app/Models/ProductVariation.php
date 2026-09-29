<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id', 'name', 'sku', 'price',
        'special_price', 'special_price_start', 'special_price_end',
        'manage_inventory', 'qty', 'stock_status',
    ];

    protected function casts(): array
    {
        return [
            'price'              => 'decimal:2',
            'special_price'     => 'decimal:2',
            'special_price_start' => 'date',
            'special_price_end'   => 'date',
            'manage_inventory'  => 'boolean',
            'qty'               => 'integer',
        ];
    }

    public function product() { return $this->belongsTo(Product::class); }

    public function images()
    {
        return $this->hasMany(ProductVariationImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductVariationImage::class)->where('is_primary', true);
    }

    public function getEffectivePriceAttribute(): float
    {
        $now = now();
        if ($this->special_price
            && (!$this->special_price_start || $this->special_price_start <= $now)
            && (!$this->special_price_end || $this->special_price_end >= $now)) {
            return (float) $this->special_price;
        }
        return (float) $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        $now = now();
        return $this->special_price
            && (!$this->special_price_start || $this->special_price_start <= $now)
            && (!$this->special_price_end || $this->special_price_end >= $now);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

    public function getIsInStockAttribute(): bool
    {
        if ($this->manage_inventory) {
            return $this->stock_status === 'in_stock' && $this->qty > 0;
        }
        return $this->stock_status === 'in_stock';
    }
}