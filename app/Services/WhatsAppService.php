<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Build standard, well-structured order summary message for WhatsApp.
     */
    public function buildOrderMessage(Order $order): string
    {
        $storeName = StoreSetting::getStoreName();
        $currency = StoreSetting::getCurrencySymbol();

        $lines = [];
        $lines[] = "🛍️ *New Order Confirmation — {$storeName}*";
        $lines[] = "─────────────────────────";
        $lines[] = "👤 *Customer:* {$order->contact_name}";
        $lines[] = "📦 *Order ID:* #{$order->order_code}";
        $lines[] = "📅 *Date:* " . $order->created_at->format('d M Y, h:i A');
        $lines[] = "💳 *Payment:* " . strtoupper($order->payment_method) . " (" . ucfirst($order->payment_status) . ")";
        $lines[] = "🚚 *Order Status:* " . ucfirst($order->status);
        $lines[] = "─────────────────────────";
        $lines[] = "*Ordered Items:*";

        foreach ($order->items as $i => $item) {
            $desc = $item->full_description;
            $lineTotal = number_format($item->line_total, 2);
            $lines[] = ($i + 1) . ". {$desc} × {$item->quantity} = {$currency}{$lineTotal}";
        }

        $lines[] = "─────────────────────────";
        $lines[] = "*Subtotal:* {$currency}" . number_format($order->subtotal, 2);
        if ($order->discount > 0) {
            $lines[] = "*Discount:* -{$currency}" . number_format($order->discount, 2);
        }
        $lines[] = "*Shipping:* " . ($order->shipping > 0 ? "{$currency}" . number_format($order->shipping, 2) : "FREE");
        $lines[] = "💰 *Grand Total:* *{$currency}" . number_format($order->total, 2) . "*";
        $lines[] = "─────────────────────────";

        if ($order->addr_line_1) {
            $lines[] = "📍 *Shipping Address:*";
            $lines[] = "{$order->addr_full_name}";
            $lines[] = "{$order->addr_line_1}" . ($order->addr_line_2 ? ", {$order->addr_line_2}" : "");
            $lines[] = "{$order->addr_city}, {$order->addr_state} - {$order->addr_pincode}";
            $lines[] = "📞 Phone: {$order->addr_mobile_primary}";
        }

        $lines[] = "";
        $lines[] = "Track your order here: " . url('/track-order?code=' . $order->order_code);
        $lines[] = "Thank you for shopping with us! 🙌";

        return implode("\n", $lines);
    }

    /**
     * Generate an instant, clickable WhatsApp URL (wa.me) for web/mobile dispatch.
     * Can target the customer or the store's WhatsApp number.
     */
    public function getClickableShareUrl(Order $order, ?string $targetPhone = null): string
    {
        $phone = $targetPhone ?: ($order->contact_mobile ?: $order->addr_mobile_primary);
        // Normalize phone number to digits with country code default (91 for India)
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (strlen($digits) === 10) {
            $digits = '91' . $digits;
        }

        $message = $this->buildOrderMessage($order);

        if (!empty($digits)) {
            return 'https://wa.me/' . $digits . '?text=' . urlencode($message);
        }

        return 'https://wa.me/?text=' . urlencode($message);
    }

    /**
     * Generate an instant, clickable WhatsApp URL (wa.me) for web/mobile dispatch.
     * Alias for getClickableShareUrl.
     */
    public function getShareUrl(Order $order, ?string $targetPhone = null): string
    {
        return $this->getClickableShareUrl($order, $targetPhone);
    }

    public static function generateOrderWaLink(Order $order, ?string $targetPhone = null): string
    {
        return (new static())->getClickableShareUrl($order, $targetPhone);
    }

    /**
     * Send automated notification using WhatsApp Cloud API / Provider if configured.
     * Does not fail hard if unconfigured or API error occurs.
     */
    public function sendAutomatedNotification(Order $order): array
    {
        $apiUrl = StoreSetting::getValue('whatsapp_api_url') ?: config('services.whatsapp.api_url');
        $token  = StoreSetting::getValue('whatsapp_api_token') ?: config('services.whatsapp.token');
        $phoneId = StoreSetting::getValue('whatsapp_phone_number_id') ?: config('services.whatsapp.phone_number_id');

        if (!$apiUrl || !$token || !$phoneId) {
            Log::info('WhatsApp automated notification skipped: API credentials not configured.');
            return [
                'success' => false,
                'message' => 'WhatsApp API not configured. Direct wa.me link available.',
                'manual_url' => $this->getClickableShareUrl($order),
            ];
        }

        try {
            $targetPhone = preg_replace('/\D+/', '', $order->contact_mobile ?: $order->addr_mobile_primary);
            if (strlen($targetPhone) === 10) {
                $targetPhone = '91' . $targetPhone;
            }

            $endpoint = rtrim($apiUrl, '/') . '/' . $phoneId . '/messages';

            $response = Http::withToken($token)
                ->timeout(10)
                ->post($endpoint, [
                    'messaging_product' => 'whatsapp',
                    'to'                => $targetPhone,
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => true,
                        'body'        => $this->buildOrderMessage($order),
                    ],
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp order notification sent to {$targetPhone} for order #{$order->order_code}");
                return ['success' => true, 'response' => $response->json()];
            }

            Log::warning("WhatsApp API notification failed: " . $response->body());
            return ['success' => false, 'error' => $response->body()];
        } catch (\Throwable $e) {
            Log::error('WhatsApp API notification exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
