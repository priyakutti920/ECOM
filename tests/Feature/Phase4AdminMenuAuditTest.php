<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4AdminMenuAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $customerUser;
    protected Category $parentCategory;
    protected Category $subCategory;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name'     => 'Admin Manager',
            'email'    => 'admin@phase4-audit.com',
            'is_admin' => true,
        ]);

        $this->customerUser = User::factory()->create([
            'name'     => 'Customer Buyer',
            'email'    => 'customer@phase4-audit.com',
            'is_admin' => false,
        ]);

        $this->parentCategory = Category::create([
            'name'       => 'Ethnic Wear',
            'slug'       => 'ethnic-wear',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $this->subCategory = Category::create([
            'name'       => 'Cotton Kurtas',
            'slug'       => 'cotton-kurtas',
            'parent_id'  => $this->parentCategory->id,
            'is_active'  => true,
            'sort_order' => 2,
        ]);

        $this->product = Product::create([
            'category_id' => $this->subCategory->id,
            'name'        => 'Handcrafted Cotton Kurta',
            'slug'        => 'handcrafted-cotton-kurta',
            'price'       => 1499,
            'stock'       => 15,
            'sku'         => 'P4-KURTA-01',
            'status'      => 'active',
            'is_featured' => true,
            'description' => 'Fine combed cotton kurta with traditional stitching.',
        ]);
    }

    /**
     * Test 1: Admin Panel navigation menu structure and All Products link.
     */
    public function test_admin_panel_navigation_menu_structure_and_all_products_link(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $content = $response->getContent();

        // 1. Desktop navigation menus are present
        $this->assertStringContainsString('class="admin-desktop-nav"', $content);
        $this->assertStringContainsString('Dashboard', $content);
        $this->assertStringContainsString('Orders', $content);
        $this->assertStringContainsString('Products', $content);
        $this->assertStringContainsString('Appearance', $content);
        $this->assertStringContainsString('Payments', $content);
        $this->assertStringContainsString('Users', $content);
        $this->assertStringContainsString('More', $content);

        // 2. All Products menu item is present in desktop Products dropdown
        $this->assertStringContainsString('href="' . url('admin/products') . '"', $content);
        $this->assertStringContainsString('All Products', $content);
        $this->assertStringContainsString('fa-box-open', $content);

        // 3. Confirm Products menu does NOT contain an unwanted generic "Home" link
        preg_match('/<li class="dropdown[^>]*>.*?Products\s*<span class="caret"><\/span>.*?<ul class="dropdown-menu">(.*?)<\/ul>/s', $content, $prodMatches);
        $this->assertNotEmpty($prodMatches, 'Products dropdown menu must exist');
        $prodMenuHtml = $prodMatches[1];
        $this->assertStringContainsString('All Products', $prodMenuHtml);
        $this->assertStringNotContainsString('>Home<', $prodMenuHtml);
        $this->assertStringNotContainsString('> Home<', $prodMenuHtml);

        // 4. All Products page is accessible and returns 200 OK
        $prodResponse = $this->actingAs($this->adminUser)->get(url('admin/products'));
        $prodResponse->assertStatus(200);
        $prodResponse->assertSee('Handcrafted Cotton Kurta');
    }

    /**
     * Test 2: Mobile Admin Navigation Drawer markup and functionality.
     */
    public function test_mobile_admin_navigation_drawer_markup_and_functionality(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $content = $response->getContent();

        // 1. Mobile toggle button
        $this->assertStringContainsString('id="adminDrawerToggle"', $content);
        $this->assertStringContainsString('class="admin-drawer-toggle"', $content);
        $this->assertStringContainsString('aria-label="Toggle navigation drawer"', $content);

        // 2. Mobile drawer and backdrop
        $this->assertStringContainsString('id="adminMobileDrawer"', $content);
        $this->assertStringContainsString('class="admin-mobile-drawer"', $content);
        $this->assertStringContainsString('id="adminDrawerBackdrop"', $content);
        $this->assertStringContainsString('class="admin-drawer-backdrop"', $content);

        // 3. Mobile drawer contains All Products under products-sub accordion
        $this->assertStringContainsString('id="products-sub"', $content);
        $this->assertStringContainsString('toggleDrawerSub(\'products-sub\')', $content);
        $this->assertStringContainsString('href="' . url('admin/products') . '"', $content);

        // 4. Drawer toggle and close scripts are present
        $this->assertStringContainsString('window.toggleAdminDrawer', $content);
        $this->assertStringContainsString('window.closeAdminDrawer', $content);
        $this->assertStringContainsString('window.toggleDrawerSub', $content);
        $this->assertStringContainsString('closeAdminDrawer()', $content);
    }

    /**
     * Test 3: Other Admin Panel navigation items and Appearance menu remain intact.
     */
    public function test_other_admin_navigation_items_remain_intact(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));
        $content = $response->getContent();

        // Appearance menu items
        $this->assertStringContainsString('Theme Customizer', $content);
        $this->assertStringContainsString('Files &amp; Media Library', $content);
        $this->assertStringContainsString('Store Banners', $content);
        $this->assertStringContainsString('Home Sections', $content);
        $this->assertStringContainsString('Footer Settings', $content);
        $this->assertStringContainsString('Storefront Nav Bar', $content);

        // Right side Store link (preview storefront)
        $this->assertStringContainsString('title="View Storefront"', $content);

        // Navigation Manager page works
        $navPageRes = $this->actingAs($this->adminUser)->get(route('admin.navigation.index'));
        $navPageRes->assertStatus(200);
        $navPageRes->assertSee('Standard Menu Items');
    }

    /**
     * Test 4: User Panel bottom navigation Home icon still works.
     */
    public function test_user_panel_bottom_nav_home_icon_still_works(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('class="bottom-navigation-wrap', $content);
        $this->assertStringContainsString('href="' . url('/') . '"', $content);
        $this->assertStringContainsString('<span>Home</span>', $content);
    }

    /**
     * Test 5: User Panel Categories page remains free of duplicate Home icon.
     */
    public function test_user_panel_categories_page_remains_free_of_duplicate_home_icon(): void
    {
        $response = $this->get('/category/' . $this->subCategory->slug);
        $response->assertStatus(200);
        $content = $response->getContent();

        preg_match('/<div class="row align-items-center mb-30"[^>]*>(.*?)<\/div>/s', $content, $headerMatches);
        if (!empty($headerMatches)) {
            $this->assertStringNotContainsString('svg-home', $headerMatches[1]);
        }

        $this->assertStringContainsString('<span>Home</span>', $content);
    }

    /**
     * Test 6: User side menu drawer keeps All Products while remaining free of duplicate Home link.
     */
    public function test_user_side_menu_drawer_keeps_all_products_without_home(): void
    {
        $response = $this->get('/');
        $content = $response->getContent();

        preg_match('/<aside class="sidebar-menu-wrap"[^>]*>(.*?)<\/aside>/s', $content, $drawerMatches);
        $this->assertNotEmpty($drawerMatches, 'sidebar-menu-drawer must exist');
        $drawerHtml = $drawerMatches[1];

        // Must retain All Products link
        $this->assertStringContainsString('All Products', $drawerHtml);
        $this->assertStringContainsString(url('/products'), $drawerHtml);

        // Must NOT have duplicate Home link
        $this->assertStringNotContainsString('la-home', $drawerHtml);
        $this->assertStringNotContainsString('> Home</a>', $drawerHtml);
    }

    /**
     * Test 7: Phase 1 mobile scrolling fixes remain intact across admin layouts.
     */
    public function test_phase1_scrolling_fixes_remain_intact(): void
    {
        $response = $this->actingAs($this->adminUser)->get(url('admin/products'));
        $content = $response->getContent();

        $this->assertStringContainsString('table-responsive', $content);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch', $content);
        $this->assertStringContainsString('touch-action: pan-x pan-y', $content);
    }

    /**
     * Test 8: Phase 3 review categories functionality remains intact.
     */
    public function test_phase3_review_categories_remain_intact(): void
    {
        $this->assertEquals('quality', Review::CATEGORY_QUALITY);
        $this->assertEquals('customer_service', Review::CATEGORY_CUSTOMER_SERVICE);
        $this->assertEquals('cart', Review::CATEGORY_CART);

        $res = $this->actingAs($this->customerUser, 'customer')->postJson(route('shop.reviews.store', $this->product), [
            'category' => 'quality',
            'rating'   => 5,
            'comment'  => 'Exceptional craftsmanship and quality fabric.',
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true, 'category' => 'quality', 'category_label' => 'Quality Review']);

        $saved = Review::latest('id')->first();
        $this->assertEquals('quality', $saved->category);
        $this->assertEquals('[Quality Review]', $saved->title);
    }

    /**
     * Test 9: Strict WhatsApp protection audit.
     */
    public function test_whatsapp_protection_audit(): void
    {
        $servicePath = app_path('Services/WhatsAppService.php');
        $this->assertFileExists($servicePath);

        $serviceCode = file_get_contents($servicePath);
        $this->assertStringContainsString('class WhatsAppService', $serviceCode);
        $this->assertStringContainsString('sendOrderNotificationToAdmin', $serviceCode);
    }
}
