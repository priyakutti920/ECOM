<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_PURCHASE        = 'PURCHASE';
    public const TYPE_RESTOCK         = 'RESTOCK';
    public const TYPE_RETURN          = 'RETURN';
    public const TYPE_ADJUSTMENT      = 'ADJUSTMENT';
    public const TYPE_CANCELLED_ORDER = 'CANCELLED_ORDER';

    protected $fillable = [
        'product_id',
        'variation_id',
        'type',
        'quantity',
        'previous_qty',
        'new_qty',
        'reference_type',
        'reference_id',
        'reason',
        'user_id',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'previous_qty' => 'integer',
            'new_qty'      => 'integer',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
