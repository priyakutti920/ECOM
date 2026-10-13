<?php

namespace App\Services;

use App\Models\WaTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaWalletService
{
    public const COST_PER_MESSAGE = 0.50;

    public static function getBalance(): float
    {
        $sum = WaTransaction::where('status', 'completed')
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END), 0) as balance")
            ->value('balance');

        return (float) ($sum ?? 0.0);
    }

    public static function getCostPerMessage(): float
    {
        return self::COST_PER_MESSAGE;
    }

    public static function getCostPerOrder(): float
    {
        $customerNotify = WhatsAppService::isCustomerNotifyEnabled();
        return $customerNotify ? self::COST_PER_MESSAGE * 2 : self::COST_PER_MESSAGE;
    }

    public static function canDeduct(float $amount): bool
    {
        return self::getBalance() >= $amount;
    }

    public static function deduct(float $amount, string $description = '', mixed $relatedInvoice = null): bool
    {
        return self::createTransaction('debit', $amount, $description, (string) $relatedInvoice, null, 'completed');
    }

    public static function addFunds(float $amount, string $description = '', ?string $paymentGateway = 'upi', ?string $paymentReference = null, ?float $verifiedAmount = null, ?string $gatewayStatus = null, ?string $rawGatewayResponse = null): bool
    {
        try {
            WaTransaction::create([
                'type' => 'credit',
                'amount' => $amount,
                'verified_amount' => $verifiedAmount ?? $amount,
                'gateway_status' => $gatewayStatus ?? 'COMPLETED',
                'raw_gateway_response' => $rawGatewayResponse,
                'description' => $description,
                'payment_gateway' => $paymentGateway,
                'payment_reference' => $paymentReference,
                'status' => 'completed',
                'related_type' => 'topup',
                'related_invoice' => $paymentReference,
                'completed_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("WaWalletService: Failed to record credit transaction - " . $e->getMessage());
            return false;
        }
    }

    public static function createTransaction(string $type, float $amount, string $description = '', ?string $reference = null, ?string $gateway = null, string $status = 'completed'): bool
    {
        try {
            WaTransaction::create([
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'payment_gateway' => $gateway,
                'payment_reference' => $reference,
                'status' => $status,
                'related_type' => $type === 'credit' ? 'topup' : 'order',
                'related_invoice' => $reference,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("WaWalletService: Failed to record transaction - " . $e->getMessage());
            return false;
        }
    }

    public static function manualTransaction(string $action, float $amount, string $description = ''): array
    {
        if ($action === 'credit') {
            return [
                'success' => false,
                'message' => 'Manual credit is disabled. WhatsApp notification credits can only be added via verified UPI payment.',
            ];
        }

        if ($action !== 'debit') {
            return ['success' => false, 'message' => 'Invalid transaction type.'];
        }

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero.'];
        }

        $desc = !empty($description) ? $description : 'Manual debit adjustment by admin';
        $ref = 'MANUAL_DEBIT_' . time() . '_' . random_int(1000, 9999);

        $ok = self::createTransaction('debit', $amount, $desc, $ref, 'manual', 'completed');
        if ($ok) {
            return [
                'success' => true,
                'message' => 'Wallet balance updated successfully.',
                'new_balance' => self::getBalance(),
            ];
        }

        return ['success' => false, 'message' => 'Failed to record transaction.'];
    }

    public static function history(int $perPage = 20)
    {
        return WaTransaction::where('status', 'completed')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
