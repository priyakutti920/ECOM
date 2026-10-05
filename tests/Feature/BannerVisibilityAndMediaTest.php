<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\MediaFile;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerVisibilityAndMediaTest extends TestCase
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
            'email' => 'admin_banner_test@example.com',
        ]);

        $this->customer = User::factory()->create([
            'is_admin' => false,
            'email' => 'customer_banner_test@example.com',
        ]);
    }

    // ================================================================
    // BANNER TESTS (1-8)
    // ================================================================

    /**
     * 1. Active banner appears on storefront.
     */
    public function test_active_banner_appears_on_storefront()
    {
        $banner = Banner::create([
            'primary_text' => 'Super Summer Sale 2026',
            'tagline' => 'Up to 50% Off Everything',
            'image' => 'banners/summer.jpg',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('shop.home'));
        $response->assertStatus(200);
        $response->assertSee('Super Summer Sale 2026');
        $response->assertSee('Up to 50% Off Everything');
    }

    /**
     * 2. Inactive banner does not appear on storefront.
     */
    public function test_inactive_banner_does_not_appear_on_storefront()
    {
        $banner = Banner::create([
            'primary_text' => 'Secret Inactive Campaign',
            'tagline' => 'Should Not Be Seen',
            'image' => 'banners/hidden.jpg',
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('shop.home'));
        $response->assertStatus(200);
        $response->assertDontSee('Secret Inactive Campaign');
        $response->assertDontSee('Should Not Be Seen');
    }

    /**
     * 3. Soft-deleted banner does not appear on storefront.
     */
    public function test_soft_deleted_banner_does_not_appear_on_storefront()
    {
        $banner = Banner::create([
            'primary_text' => 'Deleted Holiday Flash Sale',
            'tagline' => 'Gone forever',
            'image' => 'banners/deleted.jpg',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Soft delete the banner
        $banner->delete();
        $this->assertSoftDeleted('banners', ['id' => $banner->id]);

        $response = $this->get(route('shop.home'));
        $response->assertStatus(200);
        $response->assertDontSee('Deleted Holiday Flash Sale');
    }

    /**
     * 4. Restored banner appears again if active.
     */
    public function test_restored_banner_appears_again_if_active()
    {
        $banner = Banner::create([
            'primary_text' => 'Revived Mega Sale',
            'tagline' => 'Back by popular demand',
            'image' => 'banners/revived.jpg',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $banner->delete();
        $this->assertSoftDeleted('banners', ['id' => $banner->id]);

        // Verify not on home
        $this->get(route('shop.home'))->assertDontSee('Revived Mega Sale');

        // Restore banner
        $banner->restore();
        $this->assertNotSoftDeleted('banners', ['id' => $banner->id]);

        // Verify visible again on home
        $response = $this->get(route('shop.home'));
        $response->assertStatus(200);
        $response->assertSee('Revived Mega Sale');
    }

    /**
     * 5. Cache invalidates after status change.
     */
    public function test_cache_invalidates_after_banner_status_change()
    {
        $banner = Banner::create([
            'primary_text' => 'Cached Flash Event',
            'tagline' => 'Live now',
            'image' => 'banners/cache_test.jpg',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Prime the cache
        $this->get(route('shop.home'))->assertSee('Cached Flash Event');
        $this->assertTrue(Cache::has('home_banners_list'));

        // Admin toggles active -> inactive
        $toggleRes = $this->actingAs($this->admin)->postJson("/admin/banners/{$banner->id}/toggle");
        $toggleRes->assertJson(['success' => true, 'is_active' => false]);

        // Cache must have been cleared
        $banner->refresh();
        $this->assertFalse($banner->is_active);

        // Storefront must NOT display it
        $freshResponse = $this->get(route('shop.home'));
        $freshResponse->assertDontSee('Cached Flash Event');
    }

    /**
     * 6. Cache invalidates after banner delete.
     */
    public function test_cache_invalidates_after_banner_delete()
    {
        $banner = Banner::create([
            'primary_text' => 'Soon To Be Deleted',
            'tagline' => 'Catch it while you can',
            'image' => 'banners/soon_del.jpg',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Prime the cache
        $this->get(route('shop.home'))->assertSee('Soon To Be Deleted');

        // Admin deletes via DELETE route
        $delRes = $this->actingAs($this->admin)->delete("/admin/banners/{$banner->id}");
        $this->assertSoftDeleted('banners', ['id' => $banner->id]);

        // Storefront must immediately reflect deletion
        $this->get(route('shop.home'))->assertDontSee('Soon To Be Deleted');
    }

    /**
     * 7. Admin can still see inactive and trashed banners in admin panel.
     */
    public function test_admin_can_view_inactive_and_trashed_banners_in_admin()
    {
        $active = Banner::create(['primary_text' => 'Admin Active', 'image' => 'a.jpg', 'is_active' => true]);
        $inactive = Banner::create(['primary_text' => 'Admin Inactive', 'image' => 'b.jpg', 'is_active' => false]);
        $trashed = Banner::create(['primary_text' => 'Admin Trashed', 'image' => 'c.jpg', 'is_active' => true]);
        $trashed->delete();

        // 1. All tab
        $resAll = $this->actingAs($this->admin)->get(route('admin.banners.index', ['status' => 'all']));
        $resAll->assertStatus(200);
        $resAll->assertSee('Admin Active');
        $resAll->assertSee('Admin Inactive');

        // 2. Inactive tab
        $resInactive = $this->actingAs($this->admin)->get(route('admin.banners.index', ['status' => 'inactive']));
        $resInactive->assertStatus(200);
        $resInactive->assertSee('Admin Inactive');
        $resInactive->assertDontSee('Admin Active');

        // 3. Trashed tab
        $resTrash = $this->actingAs($this->admin)->get(route('admin.banners.index', ['status' => 'trashed']));
        $resTrash->assertStatus(200);
        $resTrash->assertSee('Admin Trashed');
        $resTrash->assertDontSee('Admin Active');
    }

    /**
     * 8. Unauthorized user cannot modify banners.
     */
    public function test_unauthorized_user_cannot_modify_banners()
    {
        $banner = Banner::create(['primary_text' => 'Secure Banner', 'image' => 's.jpg', 'is_active' => true]);

        // Customer cannot delete
        $resCustomer = $this->actingAs($this->customer)->delete("/admin/banners/{$banner->id}");
        $resCustomer->assertStatus(302); // redirected away by admin middleware

        // Guest cannot access JSON endpoint (returns 401)
        auth()->logout();
        $resGuest = $this->postJson("/admin/banners/{$banner->id}/toggle");
        $resGuest->assertStatus(401);
    }

    // ================================================================
    // MEDIA & BULK UPLOAD TESTS (9-20)
    // ================================================================

    /**
     * 9. Single image upload works and creates MediaFile record.
     */
    public function test_single_image_upload_creates_media_file()
    {
        $file = UploadedFile::fake()->create('hero_banner.jpg', 400, 'image/jpeg');

        $response = $this->actingAs($this->admin)->post(route('admin.appearance.files.upload'), [
            'file' => $file,
            'folder' => 'banners',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('media_files', [
            'folder' => 'banners',
            'mime_type' => 'image/jpeg',
        ]);
    }

    /**
     * 10. Bulk image upload accepts multiple images and returns structured JSON.
     */
    public function test_bulk_image_upload_accepts_multiple_images()
    {
        $file1 = UploadedFile::fake()->create('lookbook_1.png', 300, 'image/png');
        $file2 = UploadedFile::fake()->create('lookbook_2.webp', 250, 'image/webp');
        $file3 = UploadedFile::fake()->create('lookbook_3.jpg', 350, 'image/jpeg');

        $response = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-upload'), [
            'files' => [$file1, $file2, $file3],
            'folder' => 'catalog',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonCount(3, 'data.uploaded');

        $this->assertEquals(3, MediaFile::where('folder', 'catalog')->count());
    }

    /**
     * 11. Invalid MIME type is rejected.
     */
    public function test_bulk_upload_rejects_invalid_mime()
    {
        $fakePdf = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-upload'), [
            'files' => [$fakePdf],
            'folder' => 'general',
        ]);

        $response->assertStatus(422);
    }

    /**
     * 12. Oversized image (> 12MB) is rejected.
     */
    public function test_oversized_image_is_rejected()
    {
        $oversized = UploadedFile::fake()->create('huge_file.jpg', 15000, 'image/jpeg'); // 15MB

        $response = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-upload'), [
            'files' => [$oversized],
            'folder' => 'general',
        ]);

        $response->assertStatus(422);
    }

    /**
     * 13. Executable PHP or script files are strictly rejected.
     */
    public function test_executable_script_upload_is_strictly_rejected()
    {
        $malicious = UploadedFile::fake()->create('exploit.php', 50, 'application/x-php');

        $response = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-upload'), [
            'files' => [$malicious],
            'folder' => 'general',
        ]);

        $response->assertStatus(422);
    }

    /**
     * 14. Duplicate filenames are handled safely without overwriting.
     */
    public function test_duplicate_filenames_handled_safely()
    {
        $file1 = UploadedFile::fake()->create('catalog_cover.jpg', 200, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('catalog_cover.jpg', 200, 'image/jpeg');

        $res1 = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-upload'), [
            'files' => [$file1],
            'folder' => 'banners',
        ]);
        $res1->assertStatus(200);

        $res2 = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-upload'), [
            'files' => [$file2],
            'folder' => 'banners',
        ]);
        $res2->assertStatus(200);

        // Both files exist with unique distinct paths
        $files = MediaFile::where('name', 'catalog_cover')->get();
        $this->assertEquals(2, $files->count());
        $this->assertNotEquals($files[0]->path, $files[1]->path);
    }

    /**
     * 15. Broken image fallback returns vector placeholder.
     */
    public function test_broken_image_returns_svg_placeholder()
    {
        $response = $this->get('/storage/missing_dir/image_not_found.png');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
    }

    /**
     * 16. Soft-delete workflow for media file.
     */
    public function test_media_file_soft_delete_workflow()
    {
        $media = MediaFile::create([
            'name' => 'Temporary Asset',
            'filename' => 'temp.jpg',
            'path' => 'temp.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'folder' => 'general',
        ]);

        $res = $this->actingAs($this->admin)->deleteJson("/admin/appearance/files/{$media->id}");
        $res->assertJson(['success' => true]);
        $this->assertSoftDeleted('media_files', ['id' => $media->id]);
    }

    /**
     * 17. Restore workflow for media file.
     */
    public function test_media_file_restore_workflow()
    {
        $media = MediaFile::create([
            'name' => 'To Restore',
            'filename' => 'restore_me.jpg',
            'path' => 'restore_me.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'folder' => 'general',
        ]);
        $media->delete();
        $this->assertSoftDeleted('media_files', ['id' => $media->id]);

        $res = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.restore', $media->id));
        $res->assertJson(['success' => true]);
        $this->assertNotSoftDeleted('media_files', ['id' => $media->id]);
    }

    /**
     * 18. Image replacement replaces physical file and updates referencing models.
     */
    public function test_image_replacement_updates_referencing_models()
    {
        // 1. Create original media file
        Storage::disk('public')->put('banners/original.jpg', 'original image content');
        $media = MediaFile::create([
            'name' => 'Original Banner Graphic',
            'filename' => 'original.jpg',
            'path' => 'banners/original.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 2048,
            'folder' => 'banners',
        ]);

        // 2. Banner references it
        $banner = Banner::create([
            'primary_text' => 'Sale with original image',
            'image' => 'banners/original.jpg',
            'is_active' => true,
        ]);

        // 3. Replace image
        $newFile = UploadedFile::fake()->create('fresh_replaced.png', 400, 'image/png');

        $res = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.replace', $media->id), [
            'file' => $newFile,
        ]);
        $res->assertJson(['success' => true]);

        // 4. Verify banner reference updated
        $banner->refresh();
        $this->assertStringContainsString('fresh-replaced', $banner->image);
    }

    /**
     * 19. Permanent delete is prevented when media file is referenced.
     */
    public function test_permanent_delete_prevented_when_media_is_referenced()
    {
        Storage::disk('public')->put('products/dress.jpg', 'dress photo');
        $media = MediaFile::create([
            'name' => 'Active Product Photo',
            'filename' => 'dress.jpg',
            'path' => 'products/dress.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 4096,
            'folder' => 'products',
        ]);

        $product = Product::create([
            'name' => 'Summer Floral Dress',
            'slug' => 'summer-floral-dress',
            'price' => 1299,
            'is_active' => true,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image' => 'products/dress.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        // Soft delete first
        $media->delete();

        // Attempt force delete
        $forceRes = $this->actingAs($this->admin)->deleteJson(route('admin.appearance.files.force-delete', $media->id));
        $forceRes->assertStatus(422);
        $forceRes->assertJsonFragment(['success' => false]);

        // File remains in trash
        $this->assertSoftDeleted('media_files', ['id' => $media->id]);
    }

    /**
     * 20. Context-aware "Use As" assignment creates banner and clears cache.
     */
    public function test_use_as_banner_creates_banner_and_invalidates_cache()
    {
        Storage::disk('public')->put('banners/promo_hero.jpg', 'hero graphic');
        $media = MediaFile::create([
            'name' => 'Promo Hero Graphic',
            'filename' => 'promo_hero.jpg',
            'path' => 'banners/promo_hero.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 8192,
            'folder' => 'banners',
        ]);

        $res = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.set-as'), [
            'file_id' => $media->id,
            'action' => 'banner',
            'banner_title' => 'Assigned Through Media Library',
        ]);
        $res->assertJson(['success' => true]);

        $banner = Banner::where('primary_text', 'Assigned Through Media Library')->first();
        $this->assertNotNull($banner);
        $this->assertEquals('banners/promo_hero.jpg', $banner->image);
        $this->assertTrue($banner->is_active);

        // Appears on storefront
        $homeRes = $this->get(route('shop.home'));
        $homeRes->assertStatus(200);
        $homeRes->assertSee('Assigned Through Media Library');
    }

    // ================================================================
    // HEADER & RESPONSIVENESS TESTS (21-25)
    // ================================================================

    /**
     * 21-25. Admin navigation rendered with responsive hamburger toggle and off-canvas drawer.
     */
    public function test_admin_navigation_contains_responsive_drawer_and_toggle()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Brand logo/text always visible
        $response->assertSee('admin-brand-link', false);

        // Mobile / Tablet Hamburger toggle button exists
        $response->assertSee('admin-drawer-toggle', false);

        // Off-canvas mobile navigation drawer exists
        $response->assertSee('adminMobileDrawer', false);

        // Core navigation links are rendered
        $response->assertSee('Dashboard');
        $response->assertSee('Orders');
        $response->assertSee('Products');
        $response->assertSee('Appearance');
        $response->assertSee('Files &amp; Media Library', false);
        $response->assertSee('Store Banners');
    }

    /**
     * 26. Trashed media file with ProductImage reference can be permanently force-deleted and detaches cleanly.
     */
    public function test_trashed_media_file_with_product_references_can_be_force_deleted()
    {
        Storage::disk('public')->put('product/gallery_sample.png', 'fake image content');

        $media = MediaFile::create([
            'name' => 'Gallery Sample',
            'filename' => 'gallery_sample.png',
            'path' => 'product/gallery_sample.png',
            'disk' => 'public',
            'mime_type' => 'image/png',
            'size' => 1024,
            'folder' => 'product',
        ]);
        $media->delete(); // soft-delete into trash

        $category = Category::create([
            'name' => 'Test Cat',
            'slug' => 'test-cat-' . uniqid(),
            'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'price' => 500,
            'category_id' => $category->id,
            'is_active' => true,
        ]);
        $prodImg = ProductImage::create([
            'product_id' => $product->id,
            'image' => 'product/gallery_sample.png',
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $this->assertSoftDeleted('media_files', ['id' => $media->id]);
        $this->assertDatabaseHas('product_images', ['id' => $prodImg->id]);

        // 1. Force delete without force=1 parameter returns 422 with reference info
        $resUnforced = $this->actingAs($this->admin)->deleteJson("/admin/appearance/files/{$media->id}/force");
        $resUnforced->assertStatus(422);
        $resUnforced->assertJsonFragment(['success' => false, 'is_referenced' => true]);

        // 2. Force delete with ?force=1 cleanly detaches references and removes file
        $res = $this->actingAs($this->admin)->deleteJson("/admin/appearance/files/{$media->id}/force?force=1");
        $res->assertOk();
        $res->assertJson(['success' => true]);

        // MediaFile must be permanently removed
        $this->assertDatabaseMissing('media_files', ['id' => $media->id]);
        // ProductImage reference must be cleanly detached
        $this->assertDatabaseMissing('product_images', ['id' => $prodImg->id]);
        // Physical file must be deleted from storage
        Storage::disk('public')->assertMissing('product/gallery_sample.png');
    }

    /**
     * 27. Bulk force delete permanently removes multiple files and cleanly detaches references.
     */
    public function test_bulk_force_delete_cleans_up_references_and_removes_files()
    {
        Storage::disk('public')->put('product/bulk1.png', 'content 1');
        Storage::disk('public')->put('product/bulk2.png', 'content 2');

        $media1 = MediaFile::create([
            'name' => 'Bulk 1',
            'filename' => 'bulk1.png',
            'path' => 'product/bulk1.png',
            'disk' => 'public',
            'size' => 100,
        ]);
        $media1->delete();

        $media2 = MediaFile::create([
            'name' => 'Bulk 2',
            'filename' => 'bulk2.png',
            'path' => 'product/bulk2.png',
            'disk' => 'public',
            'size' => 100,
        ]);
        $media2->delete();

        $category = Category::create([
            'name' => 'Bulk Cat',
            'slug' => 'bulk-cat-' . uniqid(),
            'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Bulk Product',
            'slug' => 'bulk-product-' . uniqid(),
            'price' => 200,
            'category_id' => $category->id,
            'is_active' => true,
        ]);
        $prodImg = ProductImage::create([
            'product_id' => $product->id,
            'image' => 'product/bulk1.png',
            'sort_order' => 0,
        ]);

        $res = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-action'), [
            'ids' => [$media1->id, $media2->id],
            'action' => 'force_delete',
            'force' => true,
        ]);

        $res->assertOk();
        $res->assertJson(['success' => true]);

        $this->assertDatabaseMissing('media_files', ['id' => $media1->id]);
        $this->assertDatabaseMissing('media_files', ['id' => $media2->id]);
        $this->assertDatabaseMissing('product_images', ['id' => $prodImg->id]);
    }

    /**
     * 28. Disk sync does not resurrect or duplicate soft-deleted media files.
     */
    public function test_sync_from_disk_does_not_duplicate_soft_deleted_media_files()
    {
        Storage::disk('public')->put('product/trashed_asset.png', 'sample content');

        $media = MediaFile::create([
            'name' => 'Trashed Asset',
            'filename' => 'trashed_asset.png',
            'path' => 'product/trashed_asset.png',
            'disk' => 'public',
            'size' => 500,
        ]);
        $media->delete();

        // Run sync
        MediaFile::syncFromDisk();

        // Must NOT create an active duplicate of the trashed file
        $activeDuplicateCount = MediaFile::where('path', 'product/trashed_asset.png')->count();
        $this->assertEquals(0, $activeDuplicateCount);

        // Trashed file must remain soft-deleted
        $this->assertSoftDeleted('media_files', ['id' => $media->id]);
    }
}
