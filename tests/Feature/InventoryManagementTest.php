<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;
    protected ProductVariation $variation;
    protected InventoryService $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventory = app(InventoryService::class);
        $this->user = User::factory()->create();

        $category = Category::create([
            'name'       => 'Test Category',
            'slug'       => 'test-cat',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'name'                => 'Cotton Casual Shirt',
            'slug'                => 'cotton-casual-shirt',
            'sku'                 => 'SHIRT-001',
            'price'               => 999.00,
            'qty'                 => 20,
            'low_stock_threshold' => 5,
            'stock_status'        => 'in_stock',
            'is_active'           => true,
            'is_returnable'       => true,
        ]);

        $this->variation = ProductVariation::create([
            'product_id'          => $this->product->id,
            'name'                => 'Size M',
            'sku'                 => 'SHIRT-001-M',
            'price'               => 999.00,
            'qty'                 => 10,
            'low_stock_threshold' => 3,
            'stock_status'        => 'in_stock',
            'is_active'           => true,
            'manage_inventory'    => true,
        ]);
    }

    public function test_inventory_deduction_creates_audit_ledger_records(): void
    {
        $order = Order::create([
            'customer_id'         => $this->user->id,
            'order_code'          => 'ORD-INV-01',
            'contact_name'        => 'Test Customer',
            'contact_mobile'      => '9876543210',
            'addr_full_name'      => 'Test Customer',
            'addr_line_1'         => '123 Main St',
            'addr_city'           => 'Chennai',
            'addr_state'          => 'Tamil Nadu',
            'addr_pincode'        => '600001',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 1998,
            'total'               => 1998,
            'payment_method'      => 'upi',
            'payment_status'      => 'paid',
            'status'              => 'placed',
        ]);

        $item = OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $this->product->id,
            'variation_id'   => $this->variation->id,
            'product_name'   => $this->product->name,
            'variation_name' => $this->variation->name,
            'sku'            => $this->variation->sku,
            'quantity'       => 2,
            'unit_price'     => 999.00,
            'line_total'     => 1998.00,
        ]);

        $order->load('items');

        // Deduct stock
        $this->inventory->deductForOrder($order);

        // Verify product stock updated
        $this->assertEquals(18, $this->product->fresh()->qty);
        $this->assertEquals(8, $this->variation->fresh()->qty);

        // Verify StockMovement audit records
        $movements = StockMovement::where('reference_id', $order->order_code)->get();
        $this->assertCount(2, $movements);

        $prodMove = $movements->where('variation_id', null)->first();
        $this->assertNotNull($prodMove);
        $this->assertSame(StockMovement::TYPE_PURCHASE, $prodMove->type);
        $this->assertEquals(-2, $prodMove->quantity);
        $this->assertEquals(20, $prodMove->previous_qty);
        $this->assertEquals(18, $prodMove->new_qty);

        // Verify IDEMPOTENCY: calling deductForOrder again must not deduct again
        $this->inventory->deductForOrder($order);
        $this->assertEquals(18, $this->product->fresh()->qty);
        $this->assertEquals(8, $this->variation->fresh()->qty);
        $this->assertCount(2, StockMovement::where('reference_id', $order->order_code)->get());
    }

    public function test_cancellation_restores_inventory_via_service(): void
    {
        $order = Order::create([
            'customer_id'         => $this->user->id,
            'order_code'          => 'ORD-INV-CANCEL',
            'contact_name'        => 'Test Customer',
            'contact_mobile'      => '9876543210',
            'addr_full_name'      => 'Test Customer',
            'addr_line_1'         => '123 Main St',
            'addr_city'           => 'Chennai',
            'addr_state'          => 'Tamil Nadu',
            'addr_pincode'        => '600001',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 999,
            'total'               => 999,
            'payment_method'      => 'upi',
            'payment_status'      => 'paid',
            'status'              => 'placed',
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $this->product->id,
            'variation_id'   => $this->variation->id,
            'product_name'   => $this->product->name,
            'quantity'       => 3,
            'unit_price'     => 999.00,
            'line_total'     => 2997.00,
        ]);

        $order->load('items');

        // First deduct
        $this->inventory->deductForOrder($order);
        $this->assertEquals(17, $this->product->fresh()->qty);
        $this->assertEquals(7, $this->variation->fresh()->qty);

        // Cancel order using Order::cancelWithRestock
        $order->cancelWithRestock('Customer requested cancellation', $this->user->id, $this->user->name);

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(20, $this->product->fresh()->qty);
        $this->assertEquals(10, $this->variation->fresh()->qty);

        // Verify CANCELLED_ORDER movements
        $cancelMovements = StockMovement::where('type', StockMovement::TYPE_CANCELLED_ORDER)->get();
        $this->assertCount(2, $cancelMovements);
    }

    public function test_return_restocking_is_strictly_once(): void
    {
        $order = Order::create([
            'customer_id'         => $this->user->id,
            'order_code'          => 'ORD-INV-RET',
            'contact_name'        => 'Test Customer',
            'contact_mobile'      => '9876543210',
            'addr_full_name'      => 'Test Customer',
            'addr_line_1'         => '123 Main St',
            'addr_city'           => 'Chennai',
            'addr_state'          => 'Tamil Nadu',
            'addr_pincode'        => '600001',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 999,
            'total'               => 999,
            'payment_method'      => 'upi',
            'payment_status'      => 'paid',
            'status'              => 'delivered',
        ]);

        $item = OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $this->product->id,
            'variation_id'   => $this->variation->id,
            'product_name'   => $this->product->name,
            'quantity'       => 1,
            'unit_price'     => 999.00,
            'line_total'     => 999.00,
        ]);

        $return = OrderReturn::create([
            'order_id'      => $order->id,
            'order_item_id' => $item->id,
            'customer_id'   => $this->user->id,
            'quantity'      => 1,
            'reason'        => 'Defective size',
            'status'        => 'approved',
        ]);

        $initialProductQty = $this->product->qty; // 20
        $initialVarQty = $this->variation->qty;     // 10

        // First restock
        $res1 = $this->inventory->restoreForReturn($return, $this->user->id);
        $this->assertTrue($res1);
        $this->assertNotNull($return->fresh()->restocked_at);
        $this->assertEquals($initialProductQty + 1, $this->product->fresh()->qty);
        $this->assertEquals($initialVarQty + 1, $this->variation->fresh()->qty);

        // Repeated restock call on already-restocked return
        $res2 = $this->inventory->restoreForReturn($return, $this->user->id);
        $this->assertFalse($res2);
        // Quantities must NOT have increased again
        $this->assertEquals($initialProductQty + 1, $this->product->fresh()->qty);
        $this->assertEquals($initialVarQty + 1, $this->variation->fresh()->qty);
    }

    public function test_manual_admin_stock_adjustment(): void
    {
        $movement = $this->inventory->adjustStock(
            $this->product->id,
            $this->variation->id,
            50, // new qty
            StockMovement::TYPE_RESTOCK,
            'Supplier Shipment Batch PO-99',
            $this->user->id
        );

        $this->assertEquals(50, $this->variation->fresh()->qty);
        $this->assertEquals(40, $movement->quantity); // was 10, now 50 (+40)
        $this->assertSame(StockMovement::TYPE_RESTOCK, $movement->type);
    }
}
