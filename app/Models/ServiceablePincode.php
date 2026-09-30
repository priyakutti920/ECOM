<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceablePincode extends Model
{
    protected $table = 'serviceable_pincodes';

    protected $fillable = [
        'pincode',
        'city',
        'state',
        'is_serviceable',
        'is_cod_available',
        'estimated_days',
        'courier_name',
        'last_checked_at',
    ];

    protected $casts = [
        'is_serviceable'   => 'boolean',
        'is_cod_available' => 'boolean',
        'estimated_days'   => 'integer',
        'last_checked_at'  => 'datetime',
    ];

    /**
     * Determine if cached entry is still fresh (< 7 days old).
     */
    public function isFresh(int $days = 7): bool
    {
        return $this->last_checked_at && $this->last_checked_at->gt(now()->subDays($days));
    }
}
