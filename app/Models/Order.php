<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PLACED           = 'placed';
    public const STATUS_CONFIRMED        = 'confirmed';
    public const STATUS_PROCESSING       = 'processing';
    public const STATUS_PACKED           = 'packed';
    public const STATUS_SHIPPED          = 'shipped';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED        = 'delivered';
    public const STATUS_CANCELLED        = 'cancelled';
    public const STATUS_RETURNED         = 'returned';

    public const ALL_STATUSES = [
        'placed'           => 'Placed',
        'confirmed'        => 'Confirmed',
        'processing'       => 'Processing',
        'packed'           => 'Packed',
        'shipped'          => 'Shipped',
        'out_for_delivery' => 'Out for Delivery',
        'delivered'        => 'Delivered',
        'cancelled'        => 'Cancelled',
        'returned'         => 'Returned',
    ];

    protected $fillable = [
        'order_code',
        'customer_id',
        'address_id',
        'contact_name', 'contact_mobile', 'contact_email',
        'addr_full_name', 'addr_line_1', 'addr_line_2',
        'addr_city', 'addr_state', 'addr_pincode',
        'addr_mobile_primary', 'addr_mobile_alternate', 'addr_type',
        'subtotal', 'discount', 'shipping', 'total',
        'payment_method', 'payment_status', 'payment_gateway',
        'payment_order_id', 'payment_utr', 'paid_at',
        'status',
        'custom_statuses', 'status_history',
        'cancelled_reason', 'cancelled_at',
        'refunded_amount', 'refunded_at', 'refund_reference',
        'dispatched_via', 'tracking_number', 'dispatched_at',
        'marked_paid_at', 'marked_paid_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'       => 'decimal:2',
            'discount'       => 'decimal:2',
            'shipping'       => 'decimal:2',
            'total'          => 'decimal:2',
            'refunded_amount'=> 'decimal:2',
            'paid_at'        => 'datetime',
            'cancelled_at'   => 'datetime',
            'refunded_at'    => 'datetime',
            'dispatched_at'  => 'datetime',
            'marked_paid_at' => 'datetime',
            'custom_statuses'=> 'array',
            'status_history' => 'array',
        ];
    }

    // ── Lifecycle helpers ───────────────────────────────

    /**
     * Append a free-form custom status (admin-typed note) and append to history.
     * Returns the saved model for chaining.
     */
    public function appendCustomStatus(string $message, ?int $byUserId = null, ?string $byUserName = null): self
    {
        $now      = now()->toDateTimeString();
        $existing = $this->custom_statuses ?? [];
        $existing[] = [
            'message' => $message,
            'by'      => $byUserName,
            'by_id'   => $byUserId,
            'at'      => $now,
        ];
        $this->custom_statuses = $existing;

        $this->pushHistory('custom', $message, $byUserId, $byUserName);

        return $this;
    }

    /**
     * Append a system status change (accepted/dispatched/cancelled/etc.) to history.
     */
    public function pushHistory(string $event, string $detail = '', ?int $byUserId = null, ?string $byUserName = null): self
    {
        $hist = $this->status_history ?? [];
        $hist[] = [
            'event'   => $event,
            'detail'  => $detail,
            'by'      => $byUserName,
            'by_id'   => $byUserId,
            'at'      => now()->toDateTimeString(),
        ];
        $this->status_history = $hist;
        return $this;
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForCustomer(Builder $q, int $customerId): Builder
    {
        return $q->where('customer_id', $customerId);
    }

    public function scopePaid(Builder $q): Builder
    {
        return $q->where('payment_status', 'paid');
    }

    // ── Relationships ───────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'address_id');
    }

    public function markedPaidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_paid_by');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    /**
     * Cancel the order, replenish all item inventory, and restore/revoke coupons safely.
     */
    public function cancelWithRestock(string $reason, ?int $userId = null, ?string $userName = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($reason, $userId, $userName) {
            $this->status           = self::STATUS_CANCELLED;
            $this->cancelled_reason = $reason;
            $this->cancelled_at     = now();

            $this->pushHistory('cancelled', $reason, $userId, $userName);
            $this->save();

            // 1. Replenish product and variation stock
            foreach ($this->items as $item) {
                if ($item->product_id) {
                    $p = Product::find($item->product_id);
                    if ($p) {
                        $p->increment('qty', $item->quantity);
                        if ($p->stock_status === 'out_of_stock' && $p->qty > 0) {
                            $p->update(['stock_status' => 'in_stock']);
                        }
                    }
                }
                if (!empty($item->variation_id)) {
                    $v = ProductVariation::find($item->variation_id);
                    if ($v && $v->manage_inventory) {
                        $v->increment('qty', $item->quantity);
                        if ($v->stock_status === 'out_of_stock' && $v->qty > 0) {
                            $v->update(['stock_status' => 'in_stock']);
                        }
                    }
                }
            }

            // 2. Revoke any bonus coupon issued by this order
            Coupon::where('order_id', $this->id)->whereNull('used_at')->delete();

            // 3. Restore any coupon used on this order so customer can use it again
            $usedCoupon = Coupon::where('used_in_order_id', $this->id)->first();
            if ($usedCoupon) {
                $usedCoupon->update([
                    'used_at'          => null,
                    'used_in_order_id' => null,
                    'is_active'        => true,
                ]);
            }

            // 4. Mark invoice as cancelled if exists
            Invoice::where('invoice_json', 'like', '%"order_code":"' . $this->order_code . '"%')
                ->update(['status' => 2]);
        });
    }

    public function logStatusChange(string $event, string $detail = ''): self
    {
        return $this->pushHistory($event, $detail);
    }

    // ── Code generation ─────────────────────────────────

    /**
     * Build the next friendly order code: NS0001, NS0002 ... NS9999, NS10000 ...
     * Always at least 4 digits, but grows naturally as the sequence outpaces 9999.
     */
    public static function nextOrderCode(string $prefix = 'NS'): string
    {
        $last = static::where('order_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('order_code');

        $lastNum = $last ? (int) preg_replace('/\D/', '', $last) : 0;
        $next    = $lastNum + 1;

        // Minimum 4 digits, expand to whatever's needed once we cross 9999.
        $width = max(4, strlen((string) $next));
        return $prefix . str_pad((string) $next, $width, '0', STR_PAD_LEFT);
    }
}
