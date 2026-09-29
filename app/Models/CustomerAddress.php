<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'full_name',
        'mobile_primary',
        'mobile_alternate',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'pincode',
        'type',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    // ── Relationships ───────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // ── Accessors ───────────────────────────────────────

    public function getFormattedAttribute(): string
    {
        $parts = [
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
        ];

        return collect($parts)->filter()->implode(', ') . ' – ' . $this->pincode;
    }
}
