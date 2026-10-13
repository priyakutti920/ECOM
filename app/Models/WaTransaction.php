<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaTransaction extends Model
{
    protected $table = 'wa_transactions';

    protected $fillable = [
        'type',
        'amount',
        'description',
        'payment_gateway',
        'payment_reference',
        'status',
        'related_type',
        'related_invoice',
        'completed_at',
        'verified_amount',
        'gateway_status',
        'raw_gateway_response',
    ];

    protected $casts = [
        'amount' => 'float',
        'verified_amount' => 'float',
        'completed_at' => 'datetime',
    ];
}
