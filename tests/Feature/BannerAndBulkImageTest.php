<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerAndBulkImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_banner_status_and_deactivation_persists(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $banner = Banner::create([
            'image' => 'banners/hero-sample.jpg',
            'primary_text' => 'Summer Sale 2026',
            'tagline' => 'Up to 50% off',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertTrue($banner->is_active);

        // Update with unchecked checkbox (is_active omitted from form payload)
        $response = $this->actingAs($admin)->post(route('admin.banners.update', $banner->id), [
            'primary_text' => 'Summer Sale 2026 - Updated',
            'sort_order' => 1,
            // is_active omitted intentionally
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $banner->refresh();
        $this->assertFalse($banner->is_active);

        // Reactivate by submitting is_active => 1
        $response = $this->actingAs($admin)->post(route('admin.banners.update', $banner->id), [
            'primary_text' => 'Summer Sale 2026 - Reactivated',
            'sort_order' => 1,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $banner->refresh();
        $this->assertTrue($banner->is_active);
    }

    public function test_inactive_banner_is_hidden_from_storefront_home(): void
    {
        Banner::create([
            'image' => 'banners/active-banner.jpg',
            'primary_text' => 'Exclusive Active Hero',
            'tagline' => 'Shown to customers',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Banner::create([
            'image' => 'banners/inactive-banner.jpg',
            'primary_text' => 'Hidden Inactive Special',
            'tagline' => 'Not shown anywhere',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get(route('shop.home'));
        $response->assertStatus(200);
        $response->assertSee('Exclusive Active Hero');
        $response->assertDontSee('Hidden Inactive Special');
    }

    public function test_bulk_image_preview_matches_by_sku_and_code(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $cat = Category::create([
            'name' => 'T-Shirts',
            'slug' => 't-shirts',
            'is_active' => true,
        ]);

        $prod1 = Product::create([
            'name' => 'Oversized Tee',
            'slug' => 'oversized-tee',
            'category_id' => $cat->id,
            'sku' => 'TSHIRT-OVR',
            'code' => 'PRD-101',
            'price' => 799.00,
            'is_active' => true,
        ]);

        $prod2 = Product::create([
            'name' => 'Acid Wash Tee',
            'slug' => 'acid-wash-tee',
            'category_id' => $cat->id,
            'sku' => 'TSHIRT-ACID',
            'code' => 'PRD-102',
            'price' => 899.00,
            'is_active' => true,
        ]);

        Storage::fake('public');

        // Prepare test images matching SKU with suffix, code, and an unknown file (using create instead of image to avoid GD requirement)
        $file1 = UploadedFile::fake()->create('TSHIRT-OVR_1.jpg', 20, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('PRD-102.png', 20, 'image/png');
        $file3 = UploadedFile::fake()->create('NONEXISTENT_ITEM.webp', 20, 'image/webp');

        $response = $this->actingAs($admin)->post(route('admin.products.bulk-images.preview'), [
            'images' => [$file1, $file2, $file3],
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertEquals(3, $data['counts']['total']);
        $this->assertEquals(2, $data['counts']['matched']);
        $this->assertEquals(1, $data['counts']['unmatched']);

        // Verify matches contain correct product IDs
        $matchedProductIds = collect($data['matched'])->pluck('product_id')->all();
        $this->assertContains($prod1->id, $matchedProductIds);
        $this->assertContains($prod2->id, $matchedProductIds);
    }

    public function test_bulk_image_apply_attaches_images_to_product(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $cat = Category::create([
            'name' => 'Hoodies',
            'slug' => 'hoodies',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Zip Hoodie',
            'slug' => 'zip-hoodie',
            'category_id' => $cat->id,
            'sku' => 'HOODIE-ZIP',
            'code' => 'PRD-201',
            'price' => 1999.00,
            'is_active' => true,
        ]);

        Storage::fake('public');

        $file = UploadedFile::fake()->create('HOODIE-ZIP_front.jpg', 20, 'image/jpeg');

        $previewResp = $this->actingAs($admin)->post(route('admin.products.bulk-images.preview'), [
            'images' => [$file],
        ]);

        $previewResp->assertStatus(200);
        $previewData = $previewResp->json();

        $batchId = $previewData['batch_id'];
        $tempFilename = $previewData['matched'][0]['temp_filename'];

        // Apply bulk import
        $applyResp = $this->actingAs($admin)->post(route('admin.products.bulk-images.apply'), [
            'batch_id' => $batchId,
            'assignments' => [
                [
                    'temp_filename' => $tempFilename,
                    'product_id' => $product->id,
                    'as_primary' => true,
                    'overwrite' => false,
                ]
            ],
        ]);

        $applyResp->assertStatus(200);
        $applyData = $applyResp->json();
        $this->assertTrue($applyData['success']);
        $this->assertEquals(1, $applyData['imported_count']);

        // Check Product and ProductImage in database
        $product->refresh();
        $this->assertNotEmpty($product->image);
        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'is_primary' => true,
        ]);

        // Verify temp directory was cleaned up
        $this->assertEmpty(Storage::disk('public')->files("temp_bulk/{$batchId}"));
    }
}
