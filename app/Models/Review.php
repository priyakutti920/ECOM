<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'rating',
        'title',
        'comment',
        'is_approved',
        'is_verified_purchase',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'is_verified_purchase' => 'boolean',
        ];
    }

    public const CATEGORY_QUALITY = 'quality';
    public const CATEGORY_CUSTOMER_SERVICE = 'customer_service';
    public const CATEGORY_CART = 'cart';

    public const CATEGORIES = [
        self::CATEGORY_QUALITY => 'Quality Review',
        self::CATEGORY_CUSTOMER_SERVICE => 'Customer Service Review',
        self::CATEGORY_CART => 'Cart Review',
    ];

    /**
     * Normalize incoming category input string to canonical key.
     */
    public static function normalizeCategoryKey(?string $category): string
    {
        $cat = strtolower(trim($category ?? ''));
        if (in_array($cat, [self::CATEGORY_QUALITY, 'quality_review', 'quality review'])) {
            return self::CATEGORY_QUALITY;
        }
        if (in_array($cat, [self::CATEGORY_CUSTOMER_SERVICE, 'customer_service_review', 'customer service review', 'customer_service', 'service', 'customer service'])) {
            return self::CATEGORY_CUSTOMER_SERVICE;
        }
        if (in_array($cat, [self::CATEGORY_CART, 'cart_review', 'cart review'])) {
            return self::CATEGORY_CART;
        }
        return self::CATEGORY_QUALITY;
    }

    /**
     * Format a review title with category prefix.
     */
    public static function formatTitleWithCategory(?string $title, ?string $category): string
    {
        $categoryKey = self::normalizeCategoryKey($category);
        $label = self::CATEGORIES[$categoryKey] ?? self::CATEGORIES[self::CATEGORY_QUALITY];
        $cleanTitle = trim(preg_replace('/^\[(Quality Review|Customer Service Review|Cart Review)\]\s*/i', '', $title ?? ''));

        return $cleanTitle !== '' ? "[{$label}] {$cleanTitle}" : "[{$label}]";
    }

    /**
     * Get review category key ('quality', 'customer_service', or 'cart').
     */
    public function getCategoryAttribute(): string
    {
        $rawTitle = $this->attributes['title'] ?? '';
        if (preg_match('/^\[(Quality Review|Customer Service Review|Cart Review)\]/i', $rawTitle, $matches)) {
            return self::normalizeCategoryKey($matches[1]);
        }
        return self::CATEGORY_QUALITY;
    }

    /**
     * Get human-readable category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES[self::CATEGORY_QUALITY];
    }

    /**
     * Get clean title without category prefix.
     */
    public function getDisplayTitleAttribute(): string
    {
        $rawTitle = $this->attributes['title'] ?? '';
        if (preg_match('/^\[(Quality Review|Customer Service Review|Cart Review)\]\s*(.*)$/i', $rawTitle, $matches)) {
            return trim($matches[2]);
        }
        return $rawTitle;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope reviews by category.
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        $key = self::normalizeCategoryKey($category);
        $label = self::CATEGORIES[$key];

        if ($key === self::CATEGORY_QUALITY) {
            return $query->where(function ($q) use ($label) {
                $q->where('title', 'like', "[{$label}]%")
                  ->orWhere(function ($sq) {
                      $sq->where('title', 'not like', '[' . self::CATEGORIES[self::CATEGORY_CUSTOMER_SERVICE] . ']%')
                         ->where('title', 'not like', '[' . self::CATEGORIES[self::CATEGORY_CART] . ']%');
                  });
            });
        }

        return $query->where('title', 'like', "[{$label}]%");
    }
}
