<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\WaMessageLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppService
{
    private const FAST2SMS_API_URL = 'https://www.fast2sms.com/dev/whatsapp';

    public static function getMessageId(): string
    {
        return trim((string) (config('services.whatsapp.message_id') ?: env('WA_MESSAGE_ID', '35311')));
    }

    public static function getTemplateName(): string
    {
        return trim((string) (config('services.whatsapp.template_name') ?: env('WA_TEMPLATE_NAME', 'payment_completed')));
    }

    /**
     * Resolve Fast2SMS WhatsApp credentials purely from .env / config('services.whatsapp').
     */
    public static function resolveCredentials(): array
    {
        $authKey = config('services.whatsapp.fast2sms_auth_key') ?: env('WA_FAST2SMS_AUTH_KEY', env('FAST2SMS_DEV_AUTH_KEY', ''));
        $phoneId = config('services.whatsapp.phone_number_id') ?: env('WA_PHONE_NUMBER_ID', '');
        $adminPhone = self::getAdminPhone();

        return [
            trim((string) $authKey),
            trim((string) $phoneId),
            trim((string) $adminPhone),
        ];
    }

    public static function getAdminPhone(): string
    {
        // Admin recipient comes EXCLUSIVELY from configured Admin WhatsApp Mobile Number setting
        $dbPhone = StoreSetting::getValue('wa_admin_phone') ?: StoreSetting::getValue('wa_admin_mobile');
        if (!empty($dbPhone)) {
            return trim((string) $dbPhone);
        }

        $cfgPhone = config('services.whatsapp.admin_phone');
        if ($cfgPhone !== null) {
            return trim((string) $cfgPhone);
        }

        $envPhone = env('WA_ADMIN_PHONE', env('WA_ADMIN_MOBILE', ''));
        return trim((string) $envPhone);
    }

    public static function getSenderNumber(): string
    {
        return trim((string) (config('services.whatsapp.sender_number') ?: env('WA_SENDER_NUMBER', '')));
    }

    public static function isDisabled(): bool
    {
        return (bool) (config('services.whatsapp.engine_disabled') || env('WA_ENGINE_DISABLED', false));
    }

    public static function isCustomerNotifyEnabled(): bool
    {
        return (bool) (config('services.whatsapp.customer_notify_enabled') || env('WA_CUSTOMER_NOTIFY_ENABLED', false));
    }

    /**
     * Normalize phone number to 12 digits (with country code 91 for India by default).
     */
    public static function normalizePhone(?string $raw): string
    {
        return StoreSetting::normalizePhoneNumber($raw);
    }

    /**
     * Send order notification to admin via Fast2SMS WhatsApp API.
     * Recipient is strictly the configured Admin WhatsApp Mobile Number (NEVER the store number).
     */
    public static function sendOrderNotificationToAdmin(Order|Invoice|array $order, string $source = 'checkout'): bool
    {
        if (self::isDisabled()) {
            Log::info("WhatsAppService: Engine is disabled via config or settings.");
            return false;
        }

        $orderData = self::normalizeOrderData($order);
        if (empty($orderData)) {
            return false;
        }

        $adminPhone = self::getAdminPhone();
        if (empty($adminPhone)) {
            Log::error("WhatsAppService: Admin WhatsApp Mobile Number is not configured. Admin notification will NOT be sent.");
            WaMessageLog::createLog([
                'telephone' => '',
                'template_name' => self::getTemplateName(),
                'order_id' => $orderData['id'] ?? null,
                'invoice_id' => $orderData['invoice_id'] ?? null,
                'status' => 'failed',
                'source' => $source . '_admin_missing_config',
                'response_body' => ['error' => 'Admin WhatsApp Mobile Number is not configured in settings or environment.'],
            ]);
            return false;
        }

        $normalizedPhone = self::normalizePhone($adminPhone);
        if (strlen($normalizedPhone) < 10) {
            Log::error("WhatsAppService: Configured Admin WhatsApp Mobile Number [{$adminPhone}] is invalid.");
            WaMessageLog::createLog([
                'telephone' => $adminPhone,
                'template_name' => self::getTemplateName(),
                'order_id' => $orderData['id'] ?? null,
                'invoice_id' => $orderData['invoice_id'] ?? null,
                'status' => 'failed',
                'source' => $source . '_admin_invalid_phone',
                'response_body' => ['error' => "Configured Admin WhatsApp Mobile Number [{$adminPhone}] is invalid."],
            ]);
            return false;
        }

        [$authKey, $phoneId] = self::resolveCredentials();
        if (empty($authKey) || empty($phoneId)) {
            Log::warning("WhatsAppService: Missing Fast2SMS API credentials (authKey/phoneId).");
            return false;
        }

        // Wallet balance check and deduction (Rs 0.50)
        $cost = WaWalletService::getCostPerMessage();
        if (WaWalletService::canDeduct($cost)) {
            WaWalletService::deduct($cost, "Admin WhatsApp notification for #{$orderData['order_code']}", $orderData['order_code']);
        }

        $orderId = str_replace(['|', "\n"], ['-', ' '], $orderData['order_code']);
        $customerName = str_replace(['|', "\n"], ['-', ' '], $orderData['customer_name'] ?: 'Customer');
        $customerMobile = str_replace(['|', "\n"], ['-', ' '], $orderData['customer_phone'] ?: '');
        $amountStr = 'Rs.' . number_format((float) ($orderData['total_amount'] ?? 0), 2);

        $storeName = StoreSetting::getStoreName();
        $adminName = str_replace(['|', "\n"], ['-', ' | '], $storeName);

        $messageId = self::getMessageId();
        $templateName = self::getTemplateName();

        if ($messageId === '35311' || $templateName === 'payment_completed') {
            $cleanAmount = is_numeric($orderData['total_amount'] ?? 0)
                ? (float) $orderData['total_amount']
                : (float) preg_replace('/[^\d.]/', '', (string) ($orderData['total_amount'] ?? 0));
            $variables = number_format($cleanAmount ?: 1.00, 2);
        } elseif ($messageId === '37021' || $messageId === '37022') {
            $firstItem = $orderData['items'][0] ?? [];
            $itemName = $firstItem['name'] ?? 'Apparel';
            $itemQty = (string) ($firstItem['qty'] ?? '1');
            $variables = implode('|', [
                $storeName,
                $orderId,
                $customerName,
                $itemName,
                $itemQty,
                number_format((float) ($orderData['total_amount'] ?? 0), 2),
                $storeName,
            ]);
        } else {
            $variables = implode('|', [
                $adminName, $orderId, $customerName, $customerMobile, $amountStr,
            ]);
        }

        $pdfUrl = self::savePdfToStorage($orderData);

        $payload = [
            'authorization' => $authKey,
            'message_id' => $messageId,
            'phone_number_id' => $phoneId,
            'numbers' => $normalizedPhone,
            'variables_values' => $variables,
        ];

        if (!empty($pdfUrl) && $messageId !== '35311') {
            $payload['media_url'] = $pdfUrl;
            $payload['document_filename'] = 'invoice_' . $orderId . '.pdf';
        }

        $response = self::callFast2SmsApi($payload);
        $ok = self::isSuccess($response);

        WaMessageLog::createLog([
            'telephone' => $normalizedPhone,
            'template_name' => $templateName,
            'order_id' => $orderData['id'] ?? null,
            'invoice_id' => $orderData['invoice_id'] ?? null,
            'status' => $ok ? 'success' : 'failed',
            'source' => $source,
            'response_body' => $response,
        ]);

        if ($ok) {
            Log::info("WhatsAppService: Admin notification sent successfully for #{$orderId}");
        } else {
            Log::warning("WhatsAppService: Failed to send admin notification for #{$orderId}: " . json_encode($response));
        }

        return $ok;
    }

    /**
     * Send payment confirmation / order confirmation to customer.
     */
    public static function sendPaymentConfirmationToCustomer(Order|Invoice|array $order, string $source = 'payment_confirm'): bool
    {
        if (self::isDisabled()) {
            return false;
        }

        $orderData = self::normalizeOrderData($order);
        if (empty($orderData)) {
            return false;
        }

        $customerPhone = trim($orderData['customer_phone'] ?? '');
        if (empty($customerPhone)) {
            return false;
        }

        $normalizedPhone = self::normalizePhone($customerPhone);
        if (strlen($normalizedPhone) < 10) {
            return false;
        }

        [$authKey, $phoneId] = self::resolveCredentials();
        if (empty($authKey) || empty($phoneId)) {
            return false;
        }

        // Wallet deduction (Rs 0.50)
        $cost = WaWalletService::getCostPerMessage();
        if (WaWalletService::canDeduct($cost)) {
            WaWalletService::deduct($cost, "Customer WhatsApp confirmation for #{$orderData['order_code']}", $orderData['order_code']);
        }

        $pdfUrl = self::savePdfToStorage($orderData);

        $storeName = StoreSetting::getStoreName();
        $orderId = $orderData['order_code'];
        $customerName = $orderData['customer_name'] ?: 'Customer';
        $amountStr = 'Rs.' . number_format((float) ($orderData['total_amount'] ?? 0), 2);

        $messageId = self::getMessageId();
        $templateName = self::getTemplateName();

        if ($messageId === '35311' || $templateName === 'payment_completed') {
            $cleanAmount = is_numeric($orderData['total_amount'] ?? 0)
                ? (float) $orderData['total_amount']
                : (float) preg_replace('/[^\d.]/', '', (string) ($orderData['total_amount'] ?? 0));
            $variables = number_format($cleanAmount ?: 1.00, 2);
        } elseif ($messageId === '37021' || $messageId === '37022') {
            $firstItem = $orderData['items'][0] ?? [];
            $itemName = $firstItem['name'] ?? 'Apparel';
            $itemQty = (string) ($firstItem['qty'] ?? '1');
            $variables = implode('|', [
                $storeName,
                $orderId,
                $customerName,
                $itemName,
                $itemQty,
                number_format((float) ($orderData['total_amount'] ?? 0), 2),
                $storeName,
            ]);
        } else {
            $variables = implode('|', [
                $storeName, $orderId, $customerName, $normalizedPhone, $amountStr,
            ]);
        }

        $pdfUrl = self::savePdfToStorage($orderData);

        $payload = [
            'authorization' => $authKey,
            'message_id' => $messageId,
            'phone_number_id' => $phoneId,
            'numbers' => $normalizedPhone,
            'variables_values' => $variables,
        ];

        if (!empty($pdfUrl) && $messageId !== '35311') {
            $payload['media_url'] = $pdfUrl;
            $payload['document_filename'] = 'invoice_' . $orderId . '.pdf';
        }

        $response = self::callFast2SmsApi($payload);
        $ok = self::isSuccess($response);

        WaMessageLog::createLog([
            'telephone' => $normalizedPhone,
            'template_name' => $templateName,
            'order_id' => $orderData['id'] ?? null,
            'invoice_id' => $orderData['invoice_id'] ?? null,
            'status' => $ok ? 'success' : 'failed',
            'source' => $source,
            'response_body' => $response,
        ]);

        return $ok;
    }

    /**
     * General customer WhatsApp message sender.
     */
    public static function sendToCustomer(string $phone, string $name, string $orderId, string $amount, string $storeName): bool
    {
        if (self::isDisabled()) {
            return false;
        }

        [$authKey, $phoneId] = self::resolveCredentials();
        if (empty($authKey) || empty($phoneId)) {
            return false;
        }

        $normalizedPhone = self::normalizePhone($phone);
        if (strlen($normalizedPhone) < 10) {
            return false;
        }

        // Wallet deduction (Rs 0.50 / fifty paise)
        $cost = WaWalletService::getCostPerMessage();
        if (WaWalletService::canDeduct($cost)) {
            WaWalletService::deduct($cost, "WhatsApp message to {$normalizedPhone} ({$orderId})", $orderId);
        }

        $messageId = self::getMessageId();
        $templateName = self::getTemplateName();

        if ($messageId === '35311' || $templateName === 'payment_completed') {
            $cleanAmount = is_numeric($amount) ? (float) $amount : (float) preg_replace('/[^\d.]/', '', (string) $amount);
            $variables = number_format($cleanAmount ?: 1.00, 2);
        } elseif ($messageId === '37021' || $messageId === '37022') {
            $variables = implode('|', [
                $storeName, $orderId, $name, 'Standard', '1', $amount, $storeName,
            ]);
        } else {
            $variables = implode('|', [
                $storeName, $orderId, $name, $normalizedPhone, $amount,
            ]);
        }

        $payload = [
            'authorization' => $authKey,
            'message_id' => $messageId,
            'phone_number_id' => $phoneId,
            'numbers' => $normalizedPhone,
            'variables_values' => $variables,
        ];

        $response = self::callFast2SmsApi($payload);
        $ok = self::isSuccess($response);

        WaMessageLog::createLog([
            'telephone' => $normalizedPhone,
            'template_name' => $templateName,
            'order_id' => null,
            'status' => $ok ? 'success' : 'failed',
            'source' => 'customer',
            'response_body' => $response,
        ]);

        return $ok;
    }

    /**
     * Send test WhatsApp notification directly to explicitly specified recipient.
     * Logs action explicitly with source: test_admin.
     */
    public static function sendTestNotification(string $recipientPhone, ?string $testOrderId = null): bool
    {
        if (self::isDisabled()) {
            return false;
        }

        [$authKey, $phoneId] = self::resolveCredentials();
        if (empty($authKey) || empty($phoneId)) {
            return false;
        }

        $normalizedPhone = self::normalizePhone($recipientPhone);
        if (strlen($normalizedPhone) < 10) {
            return false;
        }

        $orderId = $testOrderId ?: ('TEST-' . rand(1000, 9999));
        $storeName = StoreSetting::getStoreName();
        $messageId = self::getMessageId();
        $templateName = self::getTemplateName();

        if ($messageId === '35311' || $templateName === 'payment_completed') {
            $variables = '1.00';
        } elseif ($messageId === '37021' || $messageId === '37022') {
            $variables = implode('|', [
                $storeName, $orderId, 'Admin Test', 'Test Item', '1', '1.00', $storeName,
            ]);
        } else {
            $variables = implode('|', [
                $storeName, $orderId, 'Admin Test', $normalizedPhone, 'Rs.1.00',
            ]);
        }

        $payload = [
            'authorization' => $authKey,
            'message_id' => $messageId,
            'phone_number_id' => $phoneId,
            'numbers' => $normalizedPhone,
            'variables_values' => $variables,
        ];

        $response = self::callFast2SmsApi($payload);
        $ok = self::isSuccess($response);

        WaMessageLog::createLog([
            'telephone' => $normalizedPhone,
            'template_name' => $templateName,
            'order_id' => null,
            'status' => $ok ? 'success' : 'failed',
            'source' => 'test_admin',
            'response_body' => $response,
        ]);

        return $ok;
    }

    /**
     * Send automated notification on order placement (handles both Fast2SMS and Cloud API).
     */
    public function sendAutomatedNotification(Order $order): array
    {
        $adminOk = self::sendOrderNotificationToAdmin($order, 'checkout');

        $customerNotify = self::isCustomerNotifyEnabled();
        $customerOk = false;
        if ($customerNotify && !empty($order->contact_mobile ?: $order->addr_mobile_primary)) {
            $customerOk = self::sendPaymentConfirmationToCustomer($order, 'checkout_customer');
        }

        return [
            'success' => $adminOk || $customerOk,
            'admin_notified' => $adminOk,
            'customer_notified' => $customerOk,
            'manual_url' => $this->getClickableShareUrl($order),
        ];
    }

    /**
     * Build standard, well-structured order summary message for WhatsApp with Rich Layout & Emojis.
     */
    public function buildOrderMessage(Order|Invoice|array $order, ?string $customTrackUrl = null): string
    {
        return self::buildPrefilledOrderMessage($order, [], $customTrackUrl);
    }

    /**
     * Build formatted WhatsApp order pre-filled message matching exact user specification.
     */
    public static function buildPrefilledOrderMessage(Order|Invoice|array $order, array $invoiceData = [], ?string $customTrackUrl = null): string
    {
        $data = self::normalizeOrderData($order);
        $storeName = StoreSetting::getStoreName();
        $storeAddress = StoreSetting::getValue('address', 'Sivakasi, Tamil Nadu, India');
        $storePhone = StoreSetting::getValue('whatsapp_number') ?: StoreSetting::getValue('wa_number') ?: StoreSetting::getValue('contact_phone');

        $createdAt = !empty($data['created_at'])
            ? date('d-m-Y, h:i A', strtotime($data['created_at']))
            : date('d-m-Y, h:i A');

        $linesText = [];
        $totalQty = 0;
        $sNo = 1;

        foreach ($data['items'] as $item) {
            $name = trim($item['name'] ?? 'Product');
            $qty = (int) ($item['qty'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $lineTotal = (float) ($item['line_total'] ?? ($unitPrice * $qty));
            $totalQty += $qty;

            $linesText[] = "{$sNo}. {$name} | {$qty} x ₹" . number_format($unitPrice, 2) . " = ₹" . number_format($lineTotal, 2);
            $sNo++;
        }

        if (empty($linesText)) {
            $linesText[] = "1. Products Order | ₹" . number_format((float) ($data['total_amount'] ?? 0), 2);
            $totalQty = 1;
        }

        $subtotal = (float) ($data['subtotal'] ?? 0);
        $discountAmount = (float) ($data['discount'] ?? 0);
        $otherCharges = (float) ($data['shipping'] ?? 0);
        $taxAmount = (float) ($data['tax_amount'] ?? 0);
        $total = (float) ($data['total_amount'] ?? ($subtotal - $discountAmount + $otherCharges + $taxAmount));

        if (empty($customTrackUrl)) {
            $customTrackUrl = url('/track-order?code=' . urlencode($data['order_code']));
        }

        $msg = [];
        $msg[] = "🛍️ *New Order Details*";
        $msg[] = "📅 *Time:* " . $createdAt;
        $msg[] = "🔖 *Order ID:* #" . $data['order_code'];
        $msg[] = "----------------------------------------";

        $msg[] = "🏬 *Shop Details / Store Information*";
        $msg[] = "*Store:* " . $storeName;
        if (!empty($storeAddress)) {
            $msg[] = "*Address:* " . $storeAddress;
        }
        if (!empty($storePhone)) {
            $msg[] = "*Contact:* " . $storePhone;
        }
        $msg[] = "----------------------------------------";

        $msg[] = "👤 *Customer Details:*";
        $msg[] = "*Name:* " . ($data['customer_name'] ?: 'Customer');
        $msg[] = "*Phone:* " . ($data['customer_phone'] ?: '-');
        if (!empty($data['customer_email'])) {
            $msg[] = "*Email:* " . $data['customer_email'];
        }
        if (!empty($data['customer_address'])) {
            $msg[] = "*Address:* " . $data['customer_address'];
        }
        $msg[] = "----------------------------------------";

        $msg[] = "📦 *Product Details*";
        $msg[] = "*S.No | Products | Qty | Amount*";
        foreach ($linesText as $lineItem) {
            $msg[] = $lineItem;
        }
        $msg[] = "----------------------------------------";

        $msg[] = "📊 *Total Qty & Total Rs*";
        $msg[] = "*Total Qty:* " . $totalQty;
        $msg[] = "*Total Rs:* ₹" . number_format($subtotal, 2);
        $msg[] = "----------------------------------------";

        $msg[] = "💳 *Payment Details*";
        $msg[] = "*Total Qty:* " . $totalQty;
        $msg[] = "*Total Value:* ₹" . number_format($subtotal, 2);
        if ($discountAmount > 0) {
            $msg[] = "*Discounted Value:* -₹" . number_format($discountAmount, 2);
        }
        if ($otherCharges > 0) {
            $msg[] = "*Extra Charges:* +₹" . number_format($otherCharges, 2);
        }
        if ($taxAmount > 0) {
            $msg[] = "*GST:* +₹" . number_format($taxAmount, 2);
        }
        $msg[] = "*Final Value: ₹" . number_format($total, 2) . "*";
        $msg[] = "----------------------------------------";

        $msg[] = "🚚 *Track Order:*";
        $msg[] = "🔗 " . $customTrackUrl;
        $msg[] = "----------------------------------------";
        $msg[] = "🙏 *Thank you for your order!*";

        return implode("\n", $msg);
    }

    /**
     * Generate an instant, clickable WhatsApp URL (wa.me) for web/mobile dispatch.
     */
    public function getClickableShareUrl(Order|Invoice|array $order, ?string $targetPhone = null): string
    {
        $data = self::normalizeOrderData($order);
        $phone = $targetPhone ?: ($data['customer_phone'] ?? '');
        $digits = self::normalizePhone($phone);
        $message = $this->buildOrderMessage($order);

        if (!empty($digits)) {
            return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
        }

        return 'https://wa.me/?text=' . rawurlencode($message);
    }

    public function getShareUrl(Order|Invoice|array $order, ?string $targetPhone = null): string
    {
        return $this->getClickableShareUrl($order, $targetPhone);
    }

    public static function generateOrderWaLink(Order|Invoice|array $order, ?string $targetPhone = null): string
    {
        return (new static())->getClickableShareUrl($order, $targetPhone);
    }

    /**
     * Get Plugin configured WhatsApp click-to-chat URL with pre-filled message.
     */
    public static function getPluginWhatsAppUrl(string $prefilledMessage = ''): string
    {
        $waNumber = StoreSetting::getValue('wa_number') ?: StoreSetting::getValue('whatsapp_number') ?: StoreSetting::getValue('contact_phone', '');
        $cleanNumber = self::normalizePhone($waNumber);

        if (empty($cleanNumber)) {
            return '';
        }

        return 'https://api.whatsapp.com/send?phone=' . $cleanNumber . (!empty($prefilledMessage) ? ('&text=' . rawurlencode($prefilledMessage)) : '');
    }

    // ── Internal Helpers ────────────────────────────────────────────────

    private static function normalizeOrderData(mixed $order): array
    {
        if ($order instanceof Order) {
            $items = [];
            foreach ($order->items as $it) {
                $items[] = [
                    'id' => $it->product_id,
                    'name' => $it->product_name,
                    'qty' => (int) $it->quantity,
                    'unit_price' => (float) $it->unit_price,
                    'line_total' => (float) $it->line_total,
                ];
            }

            $fullAddress = trim(implode(', ', array_filter([
                $order->addr_line_1,
                $order->addr_line_2,
                $order->addr_city,
                $order->addr_state ? ($order->addr_state . ($order->addr_pincode ? ' - ' . $order->addr_pincode : '')) : $order->addr_pincode,
            ])));

            return [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'invoice_number' => $order->order_code,
                'customer_name' => $order->contact_name ?: $order->addr_full_name,
                'customer_phone' => $order->contact_mobile ?: $order->addr_mobile_primary,
                'customer_email' => $order->contact_email,
                'customer_address' => $fullAddress,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'shipping' => (float) $order->shipping,
                'tax_amount' => (float) $order->tax_amount,
                'total_amount' => (float) $order->total,
                'items' => $items,
                'created_at' => $order->created_at ? $order->created_at->toDateTimeString() : now()->toDateTimeString(),
                'invoice_id' => null,
            ];
        }

        if ($order instanceof Invoice) {
            $decoded = json_decode($order->invoice_json ?? '{}', true) ?: [];
            $lines = $decoded['items'] ?? ($decoded['lines'] ?? []);
            $items = [];
            foreach ($lines as $line) {
                $items[] = [
                    'id' => $line['id'] ?? null,
                    'name' => $line['name'] ?? 'Product',
                    'qty' => (int) ($line['qty'] ?? 1),
                    'unit_price' => (float) ($line['rate'] ?? $line['unit_price'] ?? 0),
                    'line_total' => (float) ($line['amount'] ?? $line['line_total'] ?? 0),
                ];
            }

            return [
                'id' => $order->id,
                'order_code' => $order->invoice_number,
                'invoice_number' => $order->invoice_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'customer_address' => $order->customer_address,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) ($decoded['discount_amount'] ?? 0),
                'shipping' => (float) $order->other_charges,
                'tax_amount' => (float) $order->tax_amount,
                'total_amount' => (float) $order->total_amount,
                'items' => $items,
                'created_at' => $order->created_at ? $order->created_at->toDateTimeString() : now()->toDateTimeString(),
                'invoice_id' => $order->id,
            ];
        }

        if (is_array($order)) {
            $order['order_code'] = $order['order_code'] ?? ($order['invoice_number'] ?? ('ORD-' . ($order['id'] ?? time())));
            $order['items'] = $order['items'] ?? ($order['lines'] ?? []);
            return $order;
        }

        return [];
    }

    private static function savePdfToStorage(array $orderData): ?string
    {
        try {
            $invoiceCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $orderData['order_code'] ?? ('INV-' . time()));
            $fileName = $invoiceCode . '.pdf';

            $storageDir = storage_path('app/public/invoices/');
            if (!is_dir($storageDir)) {
                @mkdir($storageDir, 0777, true);
            }

            $filePath = $storageDir . $fileName;

            // Generate simple clean PDF HTML or use DomPDF
            $storeName = StoreSetting::getStoreName();
            $storeAddress = StoreSetting::getValue('address', 'Sivakasi, Tamil Nadu, India');
            $orderDate = !empty($orderData['created_at']) ? date('d-m-Y', strtotime($orderData['created_at'])) : date('d-m-Y');

            $itemsHtml = '';
            foreach ($orderData['items'] as $idx => $it) {
                $pName = htmlspecialchars($it['name'] ?? 'Product');
                $qty = (int) ($it['qty'] ?? 1);
                $rate = number_format((float) ($it['unit_price'] ?? 0), 2);
                $amt = number_format((float) ($it['line_total'] ?? 0), 2);
                $itemsHtml .= "<tr>
                    <td style='text-align:center; padding:6px 8px; border-bottom:1px solid #eee;'>" . ($idx + 1) . "</td>
                    <td style='padding:6px 8px; border-bottom:1px solid #eee;'>{$pName}</td>
                    <td style='text-align:right; padding:6px 8px; border-bottom:1px solid #eee;'>Rs.{$rate}</td>
                    <td style='text-align:center; padding:6px 8px; border-bottom:1px solid #eee;'>{$qty}</td>
                    <td style='text-align:right; padding:6px 8px; border-bottom:1px solid #eee;'>Rs.{$amt}</td>
                </tr>";
            }

            $subtotalStr = number_format((float) ($orderData['subtotal'] ?? 0), 2);
            $totalStr = number_format((float) ($orderData['total_amount'] ?? 0), 2);

            $html = "<!DOCTYPE html>
            <html><head><meta charset='utf-8'><style>
                body { font-family: sans-serif; font-size: 12px; color: #333; margin: 20px; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
                .header h1 { margin: 0; font-size: 20px; color: #2c3e50; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th { background: #2c3e50; color: #fff; padding: 8px; font-size: 11px; text-align: left; }
                .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #777; }
            </style></head><body>
                <div class='header'>
                    <h1>" . htmlspecialchars($storeName) . "</h1>
                    <p style='margin:4px 0; color:#666;'>" . nl2br(htmlspecialchars($storeAddress)) . "</p>
                    <p style='margin:4px 0; font-weight:bold;'>Invoice: #" . htmlspecialchars($orderData['order_code']) . " | Date: {$orderDate}</p>
                </div>
                <div style='margin-bottom:15px;'>
                    <strong>Customer:</strong> " . htmlspecialchars($orderData['customer_name'] ?? 'Customer') . "<br>
                    <strong>Phone:</strong> " . htmlspecialchars($orderData['customer_phone'] ?? '-') . "<br>
                    <strong>Address:</strong> " . nl2br(htmlspecialchars($orderData['customer_address'] ?? '-')) . "
                </div>
                <table>
                    <thead><tr><th>#</th><th>Product</th><th style='text-align:right'>Rate</th><th style='text-align:center'>Qty</th><th style='text-align:right'>Total</th></tr></thead>
                    <tbody>{$itemsHtml}</tbody>
                </table>
                <div style='margin-top:15px; text-align:right; font-size:13px;'>
                    <p style='margin:3px 0;'><strong>Subtotal:</strong> Rs.{$subtotalStr}</p>
                    <p style='margin:3px 0; font-size:15px; font-weight:bold; color:#2c3e50;'><strong>Grand Total:</strong> Rs.{$totalStr}</p>
                </div>
                <div class='footer'>Thank you for shopping with " . htmlspecialchars($storeName) . "!</div>
            </body></html>";

            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
                file_put_contents($filePath, $pdf->output());
            }

            if (file_exists($filePath)) {
                return url('storage/invoices/' . $fileName);
            }

            return url('/track-order?code=' . urlencode($orderData['order_code']));
        } catch (\Throwable $e) {
            Log::error("WhatsAppService: Failed to save PDF - " . $e->getMessage());
            return url('/track-order?code=' . urlencode($orderData['order_code'] ?? ''));
        }
    }

    private static function callFast2SmsApi(array $payload): string|array|null
    {
        try {
            $ch = curl_init(self::FAST2SMS_API_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'authorization: ' . ($payload['authorization'] ?? ''),
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (\Throwable $e) {
            Log::error("WhatsAppService: Fast2SMS API call exception: " . $e->getMessage());
            return null;
        }
    }

    private static function isSuccess(mixed $body): bool
    {
        if ($body === null || $body === '') return false;
        if (is_array($body)) {
            if (($body['return'] ?? null) === true) return true;
            if (($body['status'] ?? '') === 'success') return true;
            if (isset($body['status_code']) && (int) $body['status_code'] === 200) return true;
            return false;
        }
        $trim = trim((string) $body);
        if (in_array(strtolower($trim), ['true', '1', 'ok', 'success'], true)) return true;
        $decoded = json_decode($trim, true);
        if (is_array($decoded)) {
            if (($decoded['return'] ?? null) === true) return true;
            if (($decoded['status'] ?? '') === 'success') return true;
            if (isset($decoded['status_code']) && (int) $decoded['status_code'] === 200) return true;
        }
        return false;
    }
}
