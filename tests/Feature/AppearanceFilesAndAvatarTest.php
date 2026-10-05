<?php

namespace Tests\Feature;

use App\Models\MediaFile;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AppearanceFilesAndAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_customer_can_upload_avatar()
    {
        $customer = User::factory()->create([
            'is_admin' => false,
            'name' => 'Avatar Tester',
            'email' => 'avatar@test.com',
        ]);

        $this->actingAs($customer, 'customer');

        $avatarFile = UploadedFile::fake()->create('my-photo.jpg', 50, 'image/jpeg');

        $response = $this->post(route('shop.account.profile'), [
            'name' => 'Avatar Tester Updated',
            'avatar' => $avatarFile,
        ]);

        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertNotNull($customer->avatar);
        $this->assertTrue(Storage::disk('public')->exists($customer->avatar));
        $this->assertStringContainsString('avatars/', $customer->avatar_url);

        // Account page renders avatar image
        $accountPage = $this->get(route('shop.account'));
        $accountPage->assertStatus(200);
        $accountPage->assertSee($customer->avatar_url, false);
    }

    public function test_customer_can_remove_avatar()
    {
        $customer = User::factory()->create([
            'is_admin' => false,
            'name' => 'Removal Tester',
            'avatar' => 'avatars/dummy.png',
        ]);

        Storage::disk('public')->put('avatars/dummy.png', 'test content');

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('shop.account.profile'), [
            'name' => 'Removal Tester',
            'remove_avatar' => '1',
        ]);

        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertNull($customer->avatar);
        $this->assertFalse(Storage::disk('public')->exists('avatars/dummy.png'));
        // Falls back to initials avatar
        $this->assertStringContainsString('ui-avatars.com', $customer->avatar_url);
    }

    public function test_guest_cannot_access_appearance_files()
    {
        $response = $this->get(route('admin.appearance.files.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_appearance_files_manager()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.appearance.files.index'));
        $response->assertStatus(200);
        $response->assertSee('Files &amp; Media Manager', false);
    }

    public function test_root_appearance_files_redirects_to_admin()
    {
        $response = $this->get('/appearance/files');
        $response->assertRedirect(route('admin.appearance.files.index'));
    }

    public function test_admin_can_upload_media_file()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $file = UploadedFile::fake()->create('banner_hero.jpg', 150, 'image/jpeg');

        $response = $this->actingAs($admin)->post(route('admin.appearance.files.upload'), [
            'files' => [$file],
            'folder' => 'appearance',
        ]);

        $response->assertSessionHas('success');

        $media = MediaFile::where('folder', 'appearance')->latest()->first();
        $this->assertNotNull($media);
        $this->assertTrue(Storage::disk('public')->exists($media->path));
    }

    public function test_admin_can_set_media_as_store_logo_and_it_shows_on_user_side()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Storage::disk('public')->put('settings/new_store_logo.png', 'fake image content');

        $media = MediaFile::create([
            'name' => 'New Store Logo',
            'filename' => 'new_store_logo.png',
            'path' => 'settings/new_store_logo.png',
            'disk' => 'public',
            'mime_type' => 'image/png',
            'size' => 1024,
            'folder' => 'settings',
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.appearance.files.set-as'), [
            'file_id' => $media->id,
            'action' => 'logo',
        ]);

        $response->assertJson(['success' => true]);

        $logoUrl = StoreSetting::getLogoUrl();
        $this->assertNotNull($logoUrl);
        $this->assertStringContainsString('settings/new_store_logo.png', $logoUrl);

        // Verify user side storefront displays the logo
        $homePage = $this->get(route('shop.home'));
        $homePage->assertStatus(200);
        $homePage->assertSee($logoUrl, false);
    }

    public function test_admin_can_set_media_as_store_favicon_and_it_shows_on_user_side()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Storage::disk('public')->put('settings/brand_favicon.ico', 'fake favicon content');

        $media = MediaFile::create([
            'name' => 'Brand Favicon',
            'filename' => 'brand_favicon.ico',
            'path' => 'settings/brand_favicon.ico',
            'disk' => 'public',
            'mime_type' => 'image/x-icon',
            'size' => 512,
            'folder' => 'settings',
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.appearance.files.set-as'), [
            'file_id' => $media->id,
            'action' => 'favicon',
        ]);

        $response->assertJson(['success' => true]);

        $favUrl = StoreSetting::getFaviconUrl();
        $this->assertNotNull($favUrl);
        $this->assertStringContainsString('settings/brand_favicon.ico', $favUrl);

        // Verify storefront head contains favicon
        $homePage = $this->get(route('shop.home'));
        $homePage->assertStatus(200);
        $homePage->assertSee($favUrl, false);

        // Verify admin side head contains favicon
        $adminDashboard = $this->get('/admin/dashboard');
        $adminDashboard->assertStatus(200);
        $adminDashboard->assertSee($favUrl, false);

        // Verify admin guest auth pages contain favicon
        auth()->logout();
        $this->flushSession();
        $adminLogin = $this->get(route('admin.login'));
        $adminLogin->assertStatus(200);
        $adminLogin->assertSee($favUrl, false);
    }

    public function test_admin_can_attach_media_to_product()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::first() ?: Product::create([
            'name' => 'Test T-Shirt',
            'price' => 499,
            'slug' => 'test-t-shirt',
            'is_active' => true,
        ]);

        Storage::disk('public')->put('products/tshirt_front.jpg', 'fake product photo');

        $media = MediaFile::create([
            'name' => 'T-Shirt Front',
            'filename' => 'tshirt_front.jpg',
            'path' => 'products/tshirt_front.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 2048,
            'folder' => 'products',
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.appearance.files.set-as'), [
            'file_id' => $media->id,
            'action' => 'product',
            'product_id' => $product->id,
            'is_primary' => 1,
        ]);

        $response->assertJson(['success' => true]);

        $product->refresh();
        $this->assertStringContainsString('products/tshirt_front.jpg', $product->image_url);

        // Check on storefront
        $shopPage = $this->get(route('shop.shop'));
        $shopPage->assertStatus(200);
        $shopPage->assertSee($product->image_url, false);
    }

    public function test_appearance_files_json_api_returns_media_files_for_picker()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        MediaFile::create([
            'name' => 'Modal Test Photo',
            'filename' => 'modal_photo.jpg',
            'path' => 'product/modal_photo.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'folder' => 'product',
        ]);

        $res = $this->actingAs($admin)->getJson(route('admin.appearance.files.index', ['json' => 1]));
        $res->assertStatus(200);
        $res->assertJsonFragment(['name' => 'Modal Test Photo']);
        $this->assertArrayHasKey('data', $res->json());
    }

    public function test_admin_can_attach_media_to_product_variation()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::create([
            'name' => 'Hoodie Test',
            'price' => 1299,
            'slug' => 'hoodie-test',
            'is_active' => true,
        ]);

        $var = $product->variations()->create([
            'name' => 'Red / L',
            'price' => 1299,
            'manage_inventory' => false,
            'stock_status' => 'in_stock',
            'qty' => 10,
        ]);

        $media = MediaFile::create([
            'name' => 'Red Hoodie',
            'filename' => 'red_hoodie.jpg',
            'path' => 'product/red_hoodie.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 4096,
            'folder' => 'product',
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.appearance.files.set-as'), [
            'file_id' => $media->id,
            'action' => 'variation',
            'variation_id' => $var->id,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('product_variation_images', [
            'product_variation_id' => $var->id,
            'image' => 'product/red_hoodie.jpg',
        ]);
    }

    public function test_product_create_and_edit_pages_render_media_picker_modal()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::create([
            'name' => 'Modal Check Product',
            'price' => 599,
            'slug' => 'modal-check-product',
            'is_active' => true,
        ]);

        // Create page renders media picker modal
        $createRes = $this->actingAs($admin)->get(route('admin.products.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee('id="mediaPickerModal"', false);
        $createRes->assertSee('Choose Media', false);

        // Edit page renders media picker modal
        $editRes = $this->actingAs($admin)->get(route('admin.products.edit', $product->id));
        $editRes->assertStatus(200);
        $editRes->assertSee('id="mediaPickerModal"', false);
        $editRes->assertSee('Choose Media', false);
    }
}

