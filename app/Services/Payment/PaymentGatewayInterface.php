<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Unique identifier for the gateway (e.g. 'razorpay', 'cashfree', 'upi', 'cod').
     */
    public function getSlug(): string;

    /**
     * Human-readable display name.
     */
    public function getName(): string;

    /**
     * Check if the gateway has the required API credentials configured.
     */
    public function isConfigured(): bool;

    /**
     * Create an order session upstream at the payment gateway.
     *
     * @param array $orderData [order_id, amount, customer_name, customer_mobile, customer_email, redirect_url]
     * @return array [success, gateway_order_id, payment_url, client_payload, message]
     */
    public function createOrder(array $orderData): array;

    /**
     * Query the upstream gateway for order / transaction status.
     *
     * @param string $orderId The store's temporary or finalized order ID
     * @param string|null $gatewayPaymentId The gateway payment ID (e.g. pay_xxx), if available
     * @return array [success, is_paid, is_pending, is_failed, amount, transaction_id, raw]
     */
    public function checkStatus(string $orderId, ?string $gatewayPaymentId = null): array;

    /**
     * Verify and process incoming server-to-server webhook callbacks.
     *
     * @param array $payload Webhook request body
     * @param array $headers Webhook HTTP request headers
     * @param string $rawContent Raw request body string for HMAC calculation
     * @return array [success, is_paid, is_failed, order_id, gateway_payment_id, amount, message]
     */
    public function verifyWebhook(array $payload, array $headers, string $rawContent = ''): array;

    /**
     * Verify payment signature (e.g. Razorpay client checkout response).
     */
    public function verifySignature(array $data, string $signature): bool;
}
