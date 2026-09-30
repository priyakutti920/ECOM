<?php

namespace App\Models;

use App\Traits\HasCustomAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasCustomAsset;
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'description', 'url',
        'meta_title', 'meta_description', 'slug',
        'manage_inventory', 'stock_status', 'qty',
        'is_featured', 'is_active', 'status',
        'category_id', 'sku', 'image', 'price',
        'discount_price', 'special_price',
        'special_price_start', 'special_price_end',
        'video_url', 'seo_url', 'related_products',
        'sort_order', 'meta_keywords', 'og_title',
        'og_description', 'og_image',
        'is_returnable', 'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'manage_inventory' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_returnable' => 'boolean',
            'qty' => 'integer',
            'low_stock_threshold' => 'integer',
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'special_price' => 'decimal:2',
            'special_price_start' => 'datetime',
            'special_price_end' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::forceDeleting(function (Product $product) {
            foreach ($product->images as $img) {
                if (str_starts_with($img->image, 'product/') && file_exists(public_path($img->image))) {
                    @unlink(public_path($img->image));
                } else {
                    Storage::disk('public')->delete(str_replace('storage/', '', $img->image));
                }
                $img->delete();
            }
            foreach ($product->variations as $var) {
                foreach ($var->images as $img) {
                    if (str_starts_with($img->image, 'product/') && file_exists(public_path($img->image))) {
                        @unlink(public_path($img->image));
                    } else {
                        Storage::disk('public')->delete(str_replace('storage/', '', $img->image));
                    }
                    $img->delete();
                }
            }
            $product->categories()->detach();
            $product->providers()->detach();
            $product->relatedProducts()->detach();
        });
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_status', 'in_stock');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_status', 'out_of_stock');
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('manage_inventory', true)
            ->where('stock_status', 'in_stock')
            ->whereColumn('qty', '<=', 'low_stock_threshold')
            ->where('qty', '>', 0);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    // ── Aliases for legacy column names ─────────────────

    public function getCodeAttribute(): ?string
    {
        return $this->attributes['code']
            ?? $this->attributes['sku']
            ?? null;
    }

    public function getPriceAttribute(): float
    {
        return (float) ($this->attributes['price'] ?? 0);
    }

    // ── Relationships ───────────────────────────────────

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    // Backward compat: single image field
    public function getImageUrlAttribute(): string
    {
        $primary = $this->primaryImage;
        if ($primary && $primary->image) {
            return self::resolveMediaUrl($primary->image) ?? '';
        }
        $first = $this->images->first();
        if ($first && $first->image) {
            return self::resolveMediaUrl($first->image) ?? '';
        }
        // Legacy single image column
        if (! empty($this->attributes['image'])) {
            return self::resolveMediaUrl($this->attributes['image']) ?? '';
        }

        return '';
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'product_provider')
            ->withTimestamps();
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort_order');
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class)->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->orderByDesc('created_at');
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->orderByDesc('created_at');
    }

    public function relatedProducts()
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_id')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    // ── Ratings & Reviews ────────────────────────────────

    public function getAverageRatingAttribute(): float
    {
        if (isset($this->attributes['approved_reviews_avg_rating'])) {
            return round((float) $this->attributes['approved_reviews_avg_rating'], 1);
        }
        if ($this->relationLoaded('approvedReviews')) {
            $avg = $this->approvedReviews->avg('rating');

            return $avg ? round((float) $avg, 1) : 0.0;
        }
        $avg = $this->approvedReviews()->avg('rating');

        return $avg ? round((float) $avg, 1) : 0.0;
    }

    public function getRatingCountAttribute(): int
    {
        if (isset($this->attributes['approved_reviews_count'])) {
            return (int) $this->attributes['approved_reviews_count'];
        }
        if ($this->relationLoaded('approvedReviews')) {
            return $this->approvedReviews->count();
        }

        return $this->approvedReviews()->count();
    }

    public function getRatingDistributionAttribute(): array
    {
        $counts = $this->approvedReviews()
            ->selectRaw('rating, count(*) as cnt')
            ->groupBy('rating')
            ->pluck('cnt', 'rating')
            ->all();

        $total = array_sum($counts);
        $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $pct = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        if ($total > 0) {
            foreach ($dist as $star => $v) {
                $count = (int) ($counts[$star] ?? 0);
                $dist[$star] = $count;
                $pct[$star] = (int) round(($count / $total) * 100);
            }
        }

        return ['counts' => $dist, 'percentages' => $pct, 'total' => $total];
    }

    // ── Pricing & Sale Evaluation ────────────────────────

    public function getIsOnSaleAttribute(): bool
    {
        $now = now();
        if ($this->special_price && $this->special_price > 0) {
            $startOk = ! $this->special_price_start || $this->special_price_start <= $now;
            $endOk = ! $this->special_price_end || $this->special_price_end >= $now;
            if ($startOk && $endOk && (float) $this->special_price < (float) $this->price) {
                return true;
            }
        }

        return false;
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->is_on_sale) {
            return (float) $this->special_price;
        }

        return (float) $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if ($this->is_on_sale && $this->price > 0) {
            return (int) round((((float) $this->price - (float) $this->special_price) / (float) $this->price) * 100);
        }

        return 0;
    }

    /**
     * Compute exact unit price given an optional variation and selected option IDs.
     * All price logic is strictly server-side verified.
     */
    public function calculateUnitPrice(?int $variationId = null, array $optionIds = []): float
    {
        $base = (float) $this->price;

        if ($variationId) {
            $variation = $this->variations()->find($variationId);
            if ($variation) {
                $base = $variation->effective_price;
            }
        } elseif ($this->is_on_sale) {
            $base = (float) $this->special_price;
        }

        if (! empty($optionIds)) {
            $optionsExtra = (float) $this->options()->whereIn('id', $optionIds)->sum('price');
            $base += $optionsExtra;
        }

        return round($base, 2);
    }

    // ── Accessors ───────────────────────────────────────

    public function getFormattedPriceAttribute(): string
    {
        $vars = $this->relationLoaded('variations') ? $this->variations : $this->variations;
        if ($vars && $vars->isNotEmpty()) {
            $min = (float) $vars->min('price');
            $max = (float) $vars->max('price');
            if ($min == $max) {
                return number_format($min, 2);
            }

            return number_format($min, 2).' - '.number_format($max, 2);
        }

        return number_format($this->effective_price ?? 0, 2);
    }

    public function getActiveSpecialPriceAttribute(): ?float
    {
        if ($this->is_on_sale) {
            return (float) $this->special_price;
        }

        return null;
    }

    public function getCategoryNamesAttribute(): string
    {
        return $this->categories->pluck('name')->implode(', ');
    }

    public function getStatusBadgeAttribute(): string
    {
        if (! $this->is_active) {
            return '<span class="badge badge-secondary">Inactive</span>';
        }
        if ($this->stock_status === 'out_of_stock') {
            return '<span class="badge badge-danger">Out of Stock</span>';
        }

        return '<span class="badge badge-success">Active</span>';
    }
}
