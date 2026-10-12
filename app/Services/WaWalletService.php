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

    public static function addFunds(float $amount, string $description = '', ?string $paymentGateway = 'upi', ?string $paymentReference = null): bool
    {
        return self::createTransaction('credit', $amount, $description, $paymentReference, $paymentGateway, 'completed');
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
        if (!in_array($action, ['credit', 'debit'])) {
            return ['success' => false, 'message' => 'Invalid transaction type.'];
        }
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero.'];
        }

        $desc = !empty($description) ? $description : ($action === 'credit' ? 'Manual credit by admin' : 'Manual debit by admin');
        $ref = 'MANUAL_' . time() . '_' . random_int(1000, 9999);

        $ok = self::createTransaction($action, $amount, $desc, $ref, 'manual', 'completed');
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
