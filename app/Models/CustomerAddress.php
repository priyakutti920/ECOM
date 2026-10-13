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

    /**
     * Safely derive Indian State from 6-digit postal PIN code prefix without requiring external API.
     */
    public static function getStateFromPincode(?string $pincode): string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $pincode);
        if (strlen($digits) !== 6) {
            return 'Tamil Nadu'; // Default to store home state
        }

        $prefix2 = (int) substr($digits, 0, 2);
        $prefix3 = (int) substr($digits, 0, 3);

        if ($prefix3 === 403) return 'Goa';
        if ($prefix3 === 605) return 'Puducherry';
        if ($prefix3 === 682) return 'Lakshadweep';
        if ($prefix3 === 744) return 'Andaman and Nicobar Islands';
        if (in_array($prefix3, [790, 791, 792])) return 'Arunachal Pradesh';
        if (in_array($prefix3, [793, 794])) return 'Meghalaya';
        if ($prefix3 === 795) return 'Manipur';
        if ($prefix3 === 796) return 'Mizoram';
        if (in_array($prefix3, [797, 798])) return 'Nagaland';
        if ($prefix3 === 799) return 'Tripura';

        return match (true) {
            $prefix2 === 11 => 'Delhi',
            in_array($prefix2, [12, 13]) => 'Haryana',
            in_array($prefix2, [14, 15]) => 'Punjab',
            $prefix2 === 16 => 'Chandigarh',
            $prefix2 === 17 => 'Himachal Pradesh',
            in_array($prefix2, [18, 19]) => 'Jammu and Kashmir',
            in_array($prefix2, [20, 21, 22, 23, 25, 27, 28]) => 'Uttar Pradesh',
            in_array($prefix2, [24, 26]) => 'Uttarakhand',
            in_array($prefix2, [30, 31, 32, 33, 34]) => 'Rajasthan',
            in_array($prefix2, [36, 37, 38, 39]) => 'Gujarat',
            in_array($prefix2, [40, 41, 42, 43, 44]) => 'Maharashtra',
            in_array($prefix2, [45, 46, 47, 48]) => 'Madhya Pradesh',
            $prefix2 === 49 => 'Chhattisgarh',
            $prefix2 === 50 => 'Telangana',
            in_array($prefix2, [51, 52, 53]) => 'Andhra Pradesh',
            in_array($prefix2, [56, 57, 58, 59]) => 'Karnataka',
            in_array($prefix2, [60, 61, 62, 63, 64]) => 'Tamil Nadu',
            in_array($prefix2, [67, 68, 69]) => 'Kerala',
            in_array($prefix2, [70, 71, 72, 73, 74]) => 'West Bengal',
            in_array($prefix2, [75, 76, 77]) => 'Odisha',
            $prefix2 === 78 => 'Assam',
            $prefix2 === 79 => 'North East',
            in_array($prefix2, [80, 81, 82, 84, 85]) => 'Bihar',
            $prefix2 === 83 => 'Jharkhand',
            default => 'Tamil Nadu',
        };
    }
}
