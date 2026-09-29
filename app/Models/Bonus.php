<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'name',
        'bonus_percent',
        'min_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bonus_percent' => 'decimal:2',
            'min_amount'    => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayPercentAttribute(): string
    {
        return rtrim(rtrim((string) $this->bonus_percent, '0'), '.') . '%';
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'bonus_id');
    }
}
