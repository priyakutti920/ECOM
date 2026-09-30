<?php

namespace App\Models;

use App\Services\Inventory\InventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    public const STATUS_PLACED = 'placed';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PACKED = 'packed';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_RETURNED = 'returned';

    public const ALL_STATUSES = [
        'placed' => 'Placed',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'packed' => 'Packed',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ];

    protected $fillable = [
        'order_code',
        'customer_id',
        'address_id',
        'contact_name', 'contact_mobile', 'contact_email',
        'addr_full_name', 'addr_line_1', 'addr_line_2',
        'addr_city', 'addr_state', 'addr_pincode',
        'addr_mobile_primary', 'addr_mobile_alternate', 'addr_type',
        'subtotal', 'discount', 'tax_amount', 'shipping', 'total',
        'payment_method', 'payment_status', 'payment_gateway',
        'payment_order_id', 'gateway_payment_id', 'gateway_signature',
        'payment_utr', 'paid_at',
        'status',
        'custom_statuses', 'status_history',
        'cancelled_reason', 'cancelled_at',
        'refunded_amount', 'refunded_at', 'refund_reference',
        'dispatched_via', 'shipment_id', 'awb_code', 'courier_name',
        'courier_company_id', 'shipping_label_url', 'tracking_number', 'dispatched_at',
        'marked_paid_at', 'marked_paid_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'marked_paid_at' => 'datetime',
            'custom_statuses' => 'array',
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
        $now = now()->toDateTimeString();
        $existing = $this->custom_statuses ?? [];
        $existing[] = [
            'message' => $message,
            'by' => $byUserName,
            'by_id' => $byUserId,
            'at' => $now,
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
            'event' => $event,
            'detail' => $detail,
            'by' => $byUserName,
            'by_id' => $byUserId,
            'at' => now()->toDateTimeString(),
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id')->latestOfMany();
    }

    /**
     * Cancel the order, replenish all item inventory, and restore/revoke coupons safely.
     */
    public function cancelWithRestock(string $reason, ?int $userId = null, ?string $userName = null): void
    {
        DB::transaction(function () use ($reason, $userId, $userName) {
            $this->status = self::STATUS_CANCELLED;
            $this->cancelled_reason = $reason;
            $this->cancelled_at = now();

            if (in_array($this->payment_status, ['pending', 'unpaid', null], true)) {
                $this->payment_status = 'cancelled';
            }

            $this->pushHistory('cancelled', $reason, $userId, $userName);
            $this->save();

            // 1. Replenish product and variation stock with audit ledger logging
            app(InventoryService::class)->restoreForCancelledOrder($this, $reason, $userId, $userName);

            // 2. Revoke any bonus coupon issued by this order
            Coupon::where('order_id', $this->id)->whereNull('used_at')->delete();

            // 3. Restore any coupon used on this order so customer can use it again
            $usedCoupon = Coupon::where('used_in_order_id', $this->id)->first();
            if ($usedCoupon) {
                $usedCoupon->update([
                    'used_at' => null,
                    'used_in_order_id' => null,
                    'is_active' => true,
                ]);
            }

            // 4. Mark invoice as cancelled if exists
            Invoice::where('order_id', $this->id)
                ->orWhere('invoice_json', 'like', '%"order_code":"'.$this->order_code.'"%')
                ->update(['status' => 2]);
        });
    }

    public function logStatusChange(string $event, string $detail = ''): self
    {
        return $this->pushHistory($event, $detail);
    }

    /**
     * Build a secure, unpredictable public order reference: e.g. NS-9X7K2M.
     * Prevents sequential enumeration attacks while remaining concise and customer-friendly.
     */
    public static function nextOrderCode(string $prefix = 'NS'): string
    {
        do {
            $code = $prefix.'-'.strtoupper(Str::random(6));
        } while (static::where('order_code', $code)->exists());

        return $code;
    }
}
