<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variation_id',
        'variation_name',
        'color',
        'options',
        'product_name',
        'product_slug',
        'product_image',
        'quantity',
        'unit_price',
        'line_total',
        'provider_names',
        'provider_ids',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'integer',
            'unit_price'     => 'decimal:2',
            'line_total'     => 'decimal:2',
            'provider_names' => 'array',
            'provider_ids'   => 'array',
            'options'        => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function getFullDescriptionAttribute(): string
    {
        $parts = [$this->product_name];
        if ($this->variation_name) {
            $parts[] = '(' . $this->variation_name . ')';
        }
        if ($this->color) {
            $parts[] = 'Color: ' . $this->color;
        }
        if (!empty($this->options) && is_array($this->options)) {
            $optList = array_map(function ($opt) {
                if (is_array($opt)) {
                    return ($opt['name'] ?? '') . (isset($opt['price']) && $opt['price'] > 0 ? ' (+₹' . number_format($opt['price'], 2) . ')' : '');
                }
                return (string) $opt;
            }, $this->options);
            $parts[] = '[' . implode(', ', array_filter($optList)) . ']';
        }
        return implode(' ', $parts);
    }

    /**
     * Snapshot the providers for a product at the time of order.
     * Called by the cart/checkout pipeline before persisting the item.
     */
    public function snapshotProvidersFromProduct(Product $product): self
    {
        $names = $product->providers->pluck('name')->all();
        $ids   = $product->providers->pluck('id')->all();
        $this->provider_names = array_values($names);
        $this->provider_ids   = array_values($ids);
        return $this;
    }

    use \App\Traits\HasCustomAsset;

    public function getProductImageAttribute($value): string
    {
        return self::getCustomAssetUrl($value);
    }

    /**
     * Display string for the snapshot — comma-separated provider names.
     */
    public function getProvidersDisplayAttribute(): string
    {
        $names = $this->provider_names ?? [];
        return $names ? implode(', ', $names) : '—';
    }
}
