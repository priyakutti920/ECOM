<?php

namespace App\Services\Pincode;

use App\Models\ServiceablePincode;
use App\Services\Shipping\CourierServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PincodeService
{
    public function __construct(
        protected CourierServiceInterface $courier
    ) {}

    /**
     * Check if a destination pincode is serviceable, compute ETA and COD availability.
     */
    public function check(string $pincode, float $weightKg = 0.5, bool $isCod = false): array
    {
        $cleanPincode = trim($pincode);

        if (!preg_match('/^[1-9][0-9]{5}$/', $cleanPincode)) {
            return [
                'success'        => false,
                'pincode'        => $cleanPincode,
                'serviceable'    => false,
                'cod_available'  => false,
                'estimated_days' => 0,
                'eta_date'       => null,
                'message'        => 'Please enter a valid 6-digit Indian PIN code.',
                'city'           => null,
                'state'          => null,
                'courier_name'   => null,
            ];
        }

        // 1. Check local database cache
        $cached = ServiceablePincode::where('pincode', $cleanPincode)->first();

        if ($cached && $cached->isFresh(7)) {
            $etaDays = max(1, (int) $cached->estimated_days);
            $etaDate = Carbon::now()->addDays($etaDays)->format('l, d M');

            return [
                'success'        => true,
                'pincode'        => $cached->pincode,
                'serviceable'    => (bool) $cached->is_serviceable,
                'cod_available'  => (bool) $cached->is_cod_available,
                'estimated_days' => $etaDays,
                'eta_date'       => $etaDate,
                'city'           => $cached->city,
                'state'          => $cached->state,
                'courier_name'   => $cached->courier_name,
                'message'        => $cached->is_serviceable
                    ? "Delivery by {$etaDate} (" . ($cached->courier_name ?: 'Standard Delivery') . ')'
                    : 'Sorry, this pincode is currently unserviceable.',
            ];
        }

        // 2. Fetch from Courier Partner API (Shiprocket)
        try {
            $courierResult = $this->courier->checkServiceability($cleanPincode, $weightKg, $isCod);

            $isServiceable = (bool) ($courierResult['serviceable'] ?? true);
            $isCodAvailable = (bool) ($courierResult['cod_available'] ?? true);
            $estimatedDays = max(1, (int) ($courierResult['estimated_days'] ?? 4));
            $courierName = $courierResult['courier_name'] ?? 'Express Courier';
            $city = $courierResult['city'] ?? ($cached?->city);
            $state = $courierResult['state'] ?? ($cached?->state);

            // Update or create cache record
            ServiceablePincode::updateOrCreate(
                ['pincode' => $cleanPincode],
                [
                    'city'             => $city,
                    'state'            => $state,
                    'is_serviceable'   => $isServiceable,
                    'is_cod_available' => $isCodAvailable,
                    'estimated_days'   => $estimatedDays,
                    'courier_name'     => $courierName,
                    'last_checked_at'  => now(),
                ]
            );

            $etaDate = Carbon::now()->addDays($estimatedDays)->format('l, d M');

            return [
                'success'        => true,
                'pincode'        => $cleanPincode,
                'serviceable'    => $isServiceable,
                'cod_available'  => $isCodAvailable,
                'estimated_days' => $estimatedDays,
                'eta_date'       => $etaDate,
                'city'           => $city,
                'state'          => $state,
                'courier_name'   => $courierName,
                'message'        => $isServiceable
                    ? "Delivery by {$etaDate} ({$courierName})"
                    : 'Sorry, this pincode is currently unserviceable.',
            ];
        } catch (\Throwable $e) {
            Log::error("Pincode check error for {$cleanPincode}: " . $e->getMessage());

            // Graceful fallback to default estimates
            $fallbackDays = 4;
            $etaDate = Carbon::now()->addDays($fallbackDays)->format('l, d M');

            return [
                'success'        => true,
                'pincode'        => $cleanPincode,
                'serviceable'    => true,
                'cod_available'  => true,
                'estimated_days' => $fallbackDays,
                'eta_date'       => $etaDate,
                'city'           => null,
                'state'          => null,
                'courier_name'   => 'Standard Express',
                'message'        => "Estimated delivery by {$etaDate}.",
            ];
        }
    }
}
