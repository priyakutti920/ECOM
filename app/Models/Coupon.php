<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_amount', 'max_discount',
        'customer_id', 'order_id', 'bonus_id',
        'issued_at', 'expires_at', 'used_at', 'used_in_order_id',
        'label', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'       => 'decimal:2',
            'min_amount'  => 'decimal:2',
            'max_discount'=> 'decimal:2',
            'issued_at'   => 'datetime',
            'expires_at'  => 'datetime',
            'used_at'     => 'datetime',
            'is_active'   => 'boolean',
        ];
    }

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function order(): BelongsTo    { return $this->belongsTo(Order::class, 'order_id'); }
    public function bonus(): BelongsTo    { return $this->belongsTo(Bonus::class, 'bonus_id'); }

    /** Generate a short, unique coupon code. */
    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (self::where('code', $code)->exists());
        return $code;
    }

    /** Issue a coupon to a customer for a given order/bonus. */
    public static function issue(array $attrs): self
    {
        $code = $attrs['code'] ?? self::generateUniqueCode();

        return self::create(array_merge([
            'code'      => $code,
            'type'      => 'flat',
            'value'     => 0,
            'min_amount'=> 100,
            'is_active' => true,
            'issued_at' => now(),
        ], $attrs));
    }

    /** Whether this coupon can be redeemed on a cart subtotal. */
    public function canRedeemFor(float $subtotal): bool
    {
        if (!$this->is_active) return false;
        if ($this->used_at)    return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->min_amount > 0 && $subtotal < (float) $this->min_amount) return false;
        return true;
    }

    /** Compute the discount amount for a given cart subtotal. */
    public function discountFor(float $subtotal): float
    {
        if (!$this->canRedeemFor($subtotal)) return 0.0;

        $amount = $this->type === 'percent'
            ? round($subtotal * ((float) $this->value / 100), 2)
            : round((float) $this->value, 2);

        if ($this->max_discount !== null && $amount > (float) $this->max_discount) {
            $amount = (float) $this->max_discount;
        }
        if ($amount > $subtotal) $amount = $subtotal;

        return $amount;
    }

    /** Mark as redeemed in a specific order. */
    public function markUsed(int $orderId): void
    {
        $this->used_at = now();
        $this->used_in_order_id = $orderId;
        $this->is_active = false;
        $this->save();
    }

    /** Available coupons for a customer (issued to them, not yet used). */
    public function scopeRedeemableFor($query, $customerId)
    {
        return $query
            ->where('is_active', true)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) use ($customerId) {
                $q->whereNull('customer_id')->orWhere('customer_id', $customerId);
            });
    }
}
