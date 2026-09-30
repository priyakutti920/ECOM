<?php

namespace App\Services\Shipping;

use App\Models\Order;

interface CourierServiceInterface
{
    /**
     * Create an order / shipment on the courier platform.
     * Returns an array with ['success' => bool, 'shipment_id' => string|null, 'order_id' => string|null, 'message' => string|null, 'data' => array].
     */
    public function createShipment(Order $order): array;

    /**
     * Generate or assign an AWB code to an existing shipment.
     * Returns an array with ['success' => bool, 'awb_code' => string|null, 'courier_name' => string|null, 'courier_company_id' => int|null, 'message' => string|null].
     */
    public function generateAwb(Order $order): array;

    /**
     * Get printable shipping label URL or raw PDF stream.
     * Returns an array with ['success' => bool, 'label_url' => string|null, 'message' => string|null].
     */
    public function getShippingLabel(Order $order): array;

    /**
     * Track a shipment by AWB or order.
     * Returns an array with ['success' => bool, 'current_status' => string|null, 'activities' => array, 'message' => string|null].
     */
    public function trackShipment(Order $order): array;

    /**
     * Check pincode serviceability and estimated delivery days.
     * Returns an array with ['serviceable' => bool, 'cod_available' => bool, 'estimated_days' => int, 'courier_name' => string|null, 'city' => string|null, 'state' => string|null].
     */
    public function checkServiceability(string $pincode, float $weightKg = 0.5, bool $isCod = false): array;

    /**
     * Process an incoming courier webhook payload.
     * Returns an array with ['success' => bool, 'order_code' => string|null, 'status' => string|null, 'message' => string|null].
     */
    public function handleWebhook(array $payload, array $headers = []): array;
}
