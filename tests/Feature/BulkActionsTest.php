<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\MediaFile;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Cache::flush();

        $this->admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin_bulk_test@example.com',
        ]);

        $this->customer = User::factory()->create([
            'is_admin' => false,
            'email' => 'customer_bulk_test@example.com',
        ]);
    }

    /**
     * Test bulk delete and restore on Media Files.
     */
    public function test_media_files_bulk_delete_and_restore(): void
    {
        $f1 = MediaFile::create(['name' => 'F1', 'filename' => 'f1.jpg', 'path' => 'media/f1.jpg', 'disk' => 'public', 'size' => 100]);
        $f2 = MediaFile::create(['name' => 'F2', 'filename' => 'f2.jpg', 'path' => 'media/f2.jpg', 'disk' => 'public', 'size' => 200]);
        $f3 = MediaFile::create(['name' => 'F3', 'filename' => 'f3.jpg', 'path' => 'media/f3.jpg', 'disk' => 'public', 'size' => 300]);

        // Bulk delete f1 and f2
        $res = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-action'), [
            'ids' => [$f1->id, $f2->id],
            'action' => 'delete',
        ]);

        $res->assertOk();
        $this->assertTrue($f1->fresh()->trashed());
        $this->assertTrue($f2->fresh()->trashed());
        $this->assertFalse($f3->fresh()->trashed());

        // Bulk restore f1
        $resRestore = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-action'), [
            'ids' => [$f1->id],
            'action' => 'restore',
        ]);

        $resRestore->assertOk();
        $this->assertFalse($f1->fresh()->trashed());
        $this->assertTrue($f2->fresh()->trashed());
    }

    /**
     * Test bulk force delete with reference safety on Media Files.
     */
    public function test_media_files_bulk_force_delete_with_reference_safety(): void
    {
        $fUnreferenced = MediaFile::create(['name' => 'Unref', 'filename' => 'unref.jpg', 'path' => 'media/unref.jpg', 'disk' => 'public', 'size' => 100]);
        $fReferenced = MediaFile::create(['name' => 'Ref', 'filename' => 'ref.jpg', 'path' => 'banner/ref.jpg', 'disk' => 'public', 'size' => 200]);

        // Create a banner referencing $fReferenced
        Banner::create([
            'image' => 'banner/ref.jpg',
            'primary_text' => 'Ref Banner',
            'is_active' => true,
        ]);

        $res = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-action'), [
            'ids' => [$fUnreferenced->id, $fReferenced->id],
            'action' => 'force_delete',
        ]);

        $res->assertOk();
        $res->assertJsonFragment(['affected' => 1]);
        $this->assertDatabaseMissing('media_files', ['id' => $fUnreferenced->id]);
        $this->assertDatabaseHas('media_files', ['id' => $fReferenced->id]);
    }

    /**
     * Test bulk move folder on Media Files.
     */
    public function test_media_files_bulk_move_folder(): void
    {
        $f1 = MediaFile::create(['name' => 'M1', 'filename' => 'm1.jpg', 'path' => 'media/m1.jpg', 'folder' => 'media', 'disk' => 'public', 'size' => 100]);
        $f2 = MediaFile::create(['name' => 'M2', 'filename' => 'm2.jpg', 'path' => 'media/m2.jpg', 'folder' => 'media', 'disk' => 'public', 'size' => 200]);

        $res = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-action'), [
            'ids' => [$f1->id, $f2->id],
            'action' => 'move_folder',
            'folder' => 'product',
        ]);

        $res->assertOk();
        $this->assertEquals('product', $f1->fresh()->folder);
        $this->assertEquals('product', $f2->fresh()->folder);
    }

    /**
     * Test banners bulk activate, deactivate, delete, and restore.
     */
    public function test_banners_bulk_actions_and_cache_clearing(): void
    {
        $b1 = Banner::create(['image' => 'b1.jpg', 'primary_text' => 'Banner 1', 'is_active' => true]);
        $b2 = Banner::create(['image' => 'b2.jpg', 'primary_text' => 'Banner 2', 'is_active' => true]);

        // Bulk deactivate
        $resDeact = $this->actingAs($this->admin)->postJson(route('admin.banners.bulk-action'), [
            'ids' => [$b1->id, $b2->id],
            'action' => 'deactivate',
        ]);
        $resDeact->assertOk();
        $this->assertFalse((bool)$b1->fresh()->is_active);
        $this->assertFalse((bool)$b2->fresh()->is_active);

        // Bulk activate
        $resAct = $this->actingAs($this->admin)->postJson(route('admin.banners.bulk-action'), [
            'ids' => [$b1->id],
            'action' => 'activate',
        ]);
        $resAct->assertOk();
        $this->assertTrue((bool)$b1->fresh()->is_active);
        $this->assertFalse((bool)$b2->fresh()->is_active);

        // Bulk delete
        $resDel = $this->actingAs($this->admin)->postJson(route('admin.banners.bulk-action'), [
            'ids' => [$b1->id, $b2->id],
            'action' => 'delete',
        ]);
        $resDel->assertOk();
        $this->assertTrue($b1->fresh()->trashed());
        $this->assertTrue($b2->fresh()->trashed());

        // Bulk restore
        $resRest = $this->actingAs($this->admin)->postJson(route('admin.banners.bulk-action'), [
            'ids' => [$b1->id],
            'action' => 'restore',
        ]);
        $resRest->assertOk();
        $this->assertFalse($b1->fresh()->trashed());
    }

    /**
     * Test products bulk activate, deactivate, stock, category assignment, and delete.
     */
    public function test_products_bulk_actions(): void
    {
        $catA = Category::create(['name' => 'Category A', 'slug' => 'cat-a', 'is_active' => true]);
        $catB = Category::create(['name' => 'Category B', 'slug' => 'cat-b', 'is_active' => true]);

        $p1 = Product::create(['name' => 'P1', 'slug' => 'p1', 'code' => 'P001', 'is_active' => true, 'stock_status' => 'in_stock']);
        $p2 = Product::create(['name' => 'P2', 'slug' => 'p2', 'code' => 'P002', 'is_active' => true, 'stock_status' => 'in_stock']);

        // Bulk deactivate
        $resDeact = $this->actingAs($this->admin)->postJson(route('admin.products.bulk-action'), [
            'ids' => [$p1->id, $p2->id],
            'action' => 'deactivate',
        ]);
        $resDeact->assertOk();
        $this->assertFalse((bool)$p1->fresh()->is_active);
        $this->assertFalse((bool)$p2->fresh()->is_active);

        // Bulk out of stock
        $resStock = $this->actingAs($this->admin)->postJson(route('admin.products.bulk-action'), [
            'ids' => [$p1->id, $p2->id],
            'action' => 'out_of_stock',
        ]);
        $resStock->assertOk();
        $this->assertEquals('out_of_stock', $p1->fresh()->stock_status);

        // Bulk change category
        $resCat = $this->actingAs($this->admin)->postJson(route('admin.products.bulk-action'), [
            'ids' => [$p1->id, $p2->id],
            'action' => 'change_category',
            'category_id' => $catB->id,
        ]);
        $resCat->assertOk();
        $this->assertEquals($catB->id, $p1->fresh()->category_id);
        $this->assertTrue($p1->fresh()->categories->contains('id', $catB->id));

        // Bulk delete
        $resDel = $this->actingAs($this->admin)->postJson(route('admin.products.bulk-action'), [
            'ids' => [$p1->id],
            'action' => 'delete',
        ]);
        $resDel->assertOk();
        $this->assertTrue($p1->fresh()->trashed());
        $this->assertFalse($p2->fresh()->trashed());
    }

    /**
     * Test orders bulk status update and bulk mark paid.
     */
    public function test_orders_bulk_status_update_and_mark_paid(): void
    {
        $o1 = Order::create([
            'order_code' => 'ORD-TEST-001',
            'customer_id' => $this->customer->id,
            'contact_name' => 'Test User 1',
            'contact_mobile' => '9876543210',
            'addr_full_name' => 'Test User 1',
            'addr_mobile_primary' => '9876543210',
            'subtotal' => 1200.00,
            'total' => 1200.00,
            'total_amount' => 1200.00,
            'status' => 'placed',
            'payment_status' => 'pending',
            'addr_line_1' => 'Street 1',
            'addr_city' => 'City 1',
            'addr_state' => 'State 1',
            'addr_pincode' => '600001',
        ]);

        $o2 = Order::create([
            'order_code' => 'ORD-TEST-002',
            'customer_id' => $this->customer->id,
            'contact_name' => 'Test User 2',
            'contact_mobile' => '9876543211',
            'addr_full_name' => 'Test User 2',
            'addr_mobile_primary' => '9876543211',
            'subtotal' => 1500.00,
            'total' => 1500.00,
            'total_amount' => 1500.00,
            'status' => 'placed',
            'payment_status' => 'pending',
            'addr_line_1' => 'Street 2',
            'addr_city' => 'City 2',
            'addr_state' => 'State 2',
            'addr_pincode' => '600002',
        ]);

        // Bulk status update to 'processing'
        $resStatus = $this->actingAs($this->admin)->postJson(route('admin.orders.bulk-action'), [
            'ids' => [$o1->id, $o2->id],
            'action' => 'update_status',
            'status' => 'processing',
        ]);
        $resStatus->assertOk();
        $this->assertEquals('processing', $o1->fresh()->status);
        $this->assertEquals('processing', $o2->fresh()->status);

        // Verify order history logged
        $history = $o1->fresh()->status_history ?? [];
        $this->assertNotEmpty($history);

        // Bulk mark paid
        $resPaid = $this->actingAs($this->admin)->postJson(route('admin.orders.bulk-action'), [
            'ids' => [$o1->id, $o2->id],
            'action' => 'mark_paid',
        ]);
        $resPaid->assertOk();
        $this->assertEquals('paid', $o1->fresh()->payment_status);
        $this->assertEquals('paid', $o2->fresh()->payment_status);
    }

    /**
     * Test categories bulk activate, deactivate, and delete.
     */
    public function test_categories_bulk_actions(): void
    {
        $c1 = Category::create(['name' => 'Cat 1', 'slug' => 'cat-1', 'is_active' => true, 'status' => 0]);
        $c2 = Category::create(['name' => 'Cat 2', 'slug' => 'cat-2', 'is_active' => true, 'status' => 0]);

        // Bulk deactivate
        $resDeact = $this->actingAs($this->admin)->postJson(route('admin.categories.bulk-action'), [
            'ids' => [$c1->id, $c2->id],
            'action' => 'deactivate',
        ]);
        $resDeact->assertOk();
        $this->assertFalse((bool)$c1->fresh()->is_active);
        $this->assertFalse((bool)$c2->fresh()->is_active);

        // Bulk delete
        $resDel = $this->actingAs($this->admin)->postJson(route('admin.categories.bulk-action'), [
            'ids' => [$c1->id],
            'action' => 'delete',
        ]);
        $resDel->assertOk();
        $this->assertEquals(1, $c1->fresh()->status);
        $this->assertEquals(0, $c2->fresh()->status);
    }

    /**
     * Test reviews bulk approve, reject, and delete.
     */
    public function test_reviews_bulk_actions(): void
    {
        $prod = Product::create(['name' => 'Prod Rev', 'slug' => 'prod-rev', 'code' => 'PR01', 'is_active' => true]);

        $r1 = Review::create(['product_id' => $prod->id, 'customer_name' => 'User 1', 'rating' => 5, 'comment' => 'Great!', 'is_approved' => false]);
        $r2 = Review::create(['product_id' => $prod->id, 'customer_name' => 'User 2', 'rating' => 4, 'comment' => 'Nice!', 'is_approved' => false]);

        // Bulk approve
        $resAppr = $this->actingAs($this->admin)->postJson(route('admin.reviews.bulk-action'), [
            'ids' => [$r1->id, $r2->id],
            'action' => 'approve',
        ]);
        $resAppr->assertOk();
        $this->assertTrue((bool)$r1->fresh()->is_approved);
        $this->assertTrue((bool)$r2->fresh()->is_approved);

        // Bulk reject
        $resRej = $this->actingAs($this->admin)->postJson(route('admin.reviews.bulk-action'), [
            'ids' => [$r1->id],
            'action' => 'reject',
        ]);
        $resRej->assertOk();
        $this->assertFalse((bool)$r1->fresh()->is_approved);
        $this->assertTrue((bool)$r2->fresh()->is_approved);

        // Bulk delete
        $resDel = $this->actingAs($this->admin)->postJson(route('admin.reviews.bulk-action'), [
            'ids' => [$r2->id],
            'action' => 'delete',
        ]);
        $resDel->assertOk();
        $this->assertDatabaseMissing('reviews', ['id' => $r2->id]);
    }

    /**
     * Test guest cannot perform bulk actions.
     */
    public function test_guest_cannot_perform_bulk_actions(): void
    {
        $res = $this->postJson(route('admin.products.bulk-action'), [
            'ids' => [1],
            'action' => 'activate',
        ]);
        $this->assertTrue(in_array($res->status(), [302, 401, 403]));
    }

    /**
     * Test category index auto-heals orphaned subcategories whose parent was deleted.
     */
    public function test_category_index_auto_heals_orphaned_subcategories(): void
    {
        $parent = Category::create(['name' => 'Parent Cat', 'slug' => 'parent-cat', 'is_active' => true, 'status' => 1]); // deleted parent
        $child = Category::create(['name' => 'Child Cat', 'slug' => 'child-cat', 'parent_id' => $parent->id, 'is_active' => true, 'status' => 0]);

        $res = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $res->assertOk();
        $res->assertSee('Child Cat');

        // Orphaned child must have had parent_id set to null so it's a visible root
        $this->assertNull($child->fresh()->parent_id);
    }

    /**
     * Test category destroy cascades soft-delete to its subcategories.
     */
    public function test_category_destroy_cascades_to_subcategories(): void
    {
        $parent = Category::create(['name' => 'Root Cat', 'slug' => 'root-cat', 'is_active' => true, 'status' => 0]);
        $sub = Category::create(['name' => 'Sub Cat', 'slug' => 'sub-cat', 'parent_id' => $parent->id, 'is_active' => true, 'status' => 0]);

        $res = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $parent->id));
        $res->assertOk();

        $this->assertEquals(1, $parent->fresh()->status);
        $this->assertEquals(1, $sub->fresh()->status);
    }
}
