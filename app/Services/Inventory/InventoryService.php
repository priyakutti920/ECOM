<?php

namespace App\Services\Inventory;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Deduct stock for an order across all products and variations.
     * Wrapped in database transaction with pessimistic locking and idempotency protection.
     */
    public function deductForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $itemDeductKey = "order_deduct_{$order->id}_{$item->id}";

                // Check idempotency: never deduct twice for the same item in the same order
                $alreadyDeducted = StockMovement::where('idempotency_key', $itemDeductKey . '_prod')
                    ->orWhere('idempotency_key', $itemDeductKey . '_var')
                    ->exists();
                if ($alreadyDeducted) {
                    continue;
                }

                $qty = max(1, (int) $item->quantity);

                // 1. Deduct Product stock
                if ($item->product_id) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $prevQty = (int) $product->qty;
                        $newQty = max(0, $prevQty - $qty);

                        $product->qty = $newQty;
                        if ($newQty <= 0) {
                            $product->stock_status = 'out_of_stock';
                        }
                        $product->save();

                        StockMovement::create([
                            'product_id'      => $product->id,
                            'variation_id'    => null,
                            'type'            => StockMovement::TYPE_PURCHASE,
                            'quantity'        => -$qty,
                            'previous_qty'    => $prevQty,
                            'new_qty'         => $newQty,
                            'reference_type'  => 'order',
                            'reference_id'    => $order->order_code,
                            'reason'          => "Purchased in Order #{$order->order_code}",
                            'user_id'         => $order->customer_id,
                            'idempotency_key' => $itemDeductKey . '_prod',
                        ]);
                    }
                }

                // 2. Deduct Variation stock if variation exists
                if (!empty($item->variation_id)) {
                    $variation = ProductVariation::where('id', $item->variation_id)->lockForUpdate()->first();
                    if ($variation) {
                        $prevVarQty = (int) $variation->qty;
                        $newVarQty = max(0, $prevVarQty - $qty);

                        $variation->qty = $newVarQty;
                        if ($newVarQty <= 0) {
                            $variation->stock_status = 'out_of_stock';
                        }
                        $variation->save();

                        StockMovement::create([
                            'product_id'      => $variation->product_id,
                            'variation_id'    => $variation->id,
                            'type'            => StockMovement::TYPE_PURCHASE,
                            'quantity'        => -$qty,
                            'previous_qty'    => $prevVarQty,
                            'new_qty'         => $newVarQty,
                            'reference_type'  => 'order',
                            'reference_id'    => $order->order_code,
                            'reason'          => "Purchased in Order #{$order->order_code} (Variation: {$variation->name})",
                            'user_id'         => $order->customer_id,
                            'idempotency_key' => $itemDeductKey . '_var',
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Restore stock for a cancelled order.
     * Guaranteed exactly-once execution via idempotency check.
     */
    public function restoreForCancelledOrder(Order $order, string $reason, ?int $userId = null, ?string $userName = null): void
    {
        DB::transaction(function () use ($order, $reason, $userId, $userName) {
            foreach ($order->items as $item) {
                $itemRestoreKey = "order_cancel_{$order->id}_{$item->id}";

                $alreadyRestored = StockMovement::where('idempotency_key', $itemRestoreKey . '_prod')
                    ->orWhere('idempotency_key', $itemRestoreKey . '_var')
                    ->exists();

                if ($alreadyRestored) {
                    continue;
                }

                $qty = max(1, (int) $item->quantity);

                // 1. Restore Product stock
                if ($item->product_id) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $prevQty = (int) $product->qty;
                        $newQty = $prevQty + $qty;

                        $product->qty = $newQty;
                        if ($newQty > 0 && $product->stock_status === 'out_of_stock') {
                            $product->stock_status = 'in_stock';
                        }
                        $product->save();

                        StockMovement::create([
                            'product_id'      => $product->id,
                            'variation_id'    => null,
                            'type'            => StockMovement::TYPE_CANCELLED_ORDER,
                            'quantity'        => $qty,
                            'previous_qty'    => $prevQty,
                            'new_qty'         => $newQty,
                            'reference_type'  => 'order_cancellation',
                            'reference_id'    => $order->order_code,
                            'reason'          => "Restored from cancelled Order #{$order->order_code}: {$reason}",
                            'user_id'         => $userId,
                            'idempotency_key' => $itemRestoreKey . '_prod',
                        ]);
                    }
                }

                // 2. Restore Variation stock
                if (!empty($item->variation_id)) {
                    $variation = ProductVariation::where('id', $item->variation_id)->lockForUpdate()->first();
                    if ($variation) {
                        $prevVarQty = (int) $variation->qty;
                        $newVarQty = $prevVarQty + $qty;

                        $variation->qty = $newVarQty;
                        if ($newVarQty > 0 && $variation->stock_status === 'out_of_stock') {
                            $variation->stock_status = 'in_stock';
                        }
                        $variation->save();

                        StockMovement::create([
                            'product_id'      => $variation->product_id,
                            'variation_id'    => $variation->id,
                            'type'            => StockMovement::TYPE_CANCELLED_ORDER,
                            'quantity'        => $qty,
                            'previous_qty'    => $prevVarQty,
                            'new_qty'         => $newVarQty,
                            'reference_type'  => 'order_cancellation',
                            'reference_id'    => $order->order_code,
                            'reason'          => "Restored from cancelled Order #{$order->order_code} (Variation: {$variation->name}): {$reason}",
                            'user_id'         => $userId,
                            'idempotency_key' => $itemRestoreKey . '_var',
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Restore stock when an eligible return is approved and completed.
     * Strictly executes exactly once by verifying and setting restocked_at.
     */
    public function restoreForReturn(OrderReturn $return, ?int $quantity = null, ?int $userId = null, ?string $userName = null): bool
    {
        if ($return->restocked_at !== null) {
            Log::info("OrderReturn #{$return->id} was already restocked at {$return->restocked_at}. Skipping duplicate restock.");
            return false;
        }

        return DB::transaction(function () use ($return, $quantity, $userId) {
            $item = $return->item;
            $qty = $quantity ?: ($return->quantity ?: ($item ? $item->quantity : 1));
            $orderCode = $return->order ? $return->order->order_code : 'N/A';

            // 1. Restore Product stock
            $prodId = $return->product_id ?: ($item ? $item->product_id : null);
            if ($prodId) {
                $product = Product::where('id', $prodId)->lockForUpdate()->first();
                if ($product) {
                    $prevQty = (int) $product->qty;
                    $newQty = $prevQty + $qty;

                    $product->qty = $newQty;
                    if ($newQty > 0 && $product->stock_status === 'out_of_stock') {
                        $product->stock_status = 'in_stock';
                    }
                    $product->save();

                    StockMovement::create([
                        'product_id'      => $product->id,
                        'variation_id'    => null,
                        'type'            => StockMovement::TYPE_RETURN,
                        'quantity'        => $qty,
                        'previous_qty'    => $prevQty,
                        'new_qty'         => $newQty,
                        'reference_type'  => 'order_return',
                        'reference_id'    => (string) $return->id,
                        'reason'          => "Restocked from approved return #{$return->id} (Order #{$orderCode})",
                        'user_id'         => $userId,
                        'idempotency_key' => "return_{$return->id}_prod",
                    ]);
                }
            }

            // 2. Restore Variation stock
            $varId = $item ? $item->variation_id : null;
            if ($varId) {
                $variation = ProductVariation::where('id', $varId)->lockForUpdate()->first();
                if ($variation) {
                    $prevVarQty = (int) $variation->qty;
                    $newVarQty = $prevVarQty + $qty;

                    $variation->qty = $newVarQty;
                    if ($newVarQty > 0 && $variation->stock_status === 'out_of_stock') {
                        $variation->stock_status = 'in_stock';
                    }
                    $variation->save();

                    StockMovement::create([
                        'product_id'      => $variation->product_id,
                        'variation_id'    => $variation->id,
                        'type'            => StockMovement::TYPE_RETURN,
                        'quantity'        => $qty,
                        'previous_qty'    => $prevVarQty,
                        'new_qty'         => $newVarQty,
                        'reference_type'  => 'order_return',
                        'reference_id'    => (string) $return->id,
                        'reason'          => "Restocked from approved return #{$return->id} (Variation: {$variation->name})",
                        'user_id'         => $userId,
                        'idempotency_key' => "return_{$return->id}_var",
                    ]);
                }
            }

            $return->restocked_at = now();
            $return->save();

            return true;
        });
    }

    /**
     * Manual stock adjustment / restock performed by an administrator.
     */
    public function adjustStock(int $productId, ?int $variationId, int $newQty, string $type, string $reason, ?int $userId = null): StockMovement
    {
        return DB::transaction(function () use ($productId, $variationId, $newQty, $type, $reason, $userId) {
            $newQty = max(0, $newQty);

            if ($variationId) {
                $variation = ProductVariation::where('id', $variationId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $prevQty = (int) $variation->qty;
                $diff = $newQty - $prevQty;

                $variation->qty = $newQty;
                $variation->stock_status = ($newQty <= 0) ? 'out_of_stock' : 'in_stock';
                $variation->save();

                return StockMovement::create([
                    'product_id'      => $productId,
                    'variation_id'    => $variationId,
                    'type'            => $type,
                    'quantity'        => $diff,
                    'previous_qty'    => $prevQty,
                    'new_qty'         => $newQty,
                    'reference_type'  => 'manual_adjustment',
                    'reference_id'    => (string) time(),
                    'reason'          => $reason ?: 'Manual inventory adjustment',
                    'user_id'         => $userId,
                ]);
            }

            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();
            $prevQty = (int) $product->qty;
            $diff = $newQty - $prevQty;

            $product->qty = $newQty;
            $product->stock_status = ($newQty <= 0) ? 'out_of_stock' : 'in_stock';
            $product->save();

            return StockMovement::create([
                'product_id'      => $productId,
                'variation_id'    => null,
                'type'            => $type,
                'quantity'        => $diff,
                'previous_qty'    => $prevQty,
                'new_qty'         => $newQty,
                'reference_type'  => 'manual_adjustment',
                'reference_id'    => (string) time(),
                'reason'          => $reason ?: 'Manual inventory adjustment',
                'user_id'         => $userId,
            ]);
        });
    }
}
