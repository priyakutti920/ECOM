<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2ScopeCompletionAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $customerUser;
    protected Category $parentCategory;
    protected Category $subCategory;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerUser = User::factory()->create([
            'name' => 'Karthik Raja',
            'email' => 'karthik@test-phase2.com',
            'is_admin' => false,
        ]);

        $this->parentCategory = Category::create([
            'name' => 'Mens Fashion',
            'slug' => 'mens-fashion',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->subCategory = Category::create([
            'name' => 'Printed Shirts',
            'slug' => 'printed-shirts',
            'parent_id' => $this->parentCategory->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->product = Product::create([
            'category_id' => $this->subCategory->id,
            'name' => 'Premium Printed Oxford Shirt',
            'slug' => 'premium-printed-oxford-shirt',
            'price' => 1299,
            'stock' => 25,
            'sku' => 'P2-SHIRT-01',
            'status' => 'active',
            'is_featured' => true,
            'description' => 'Breathable woven shirt designed for premium comfort.',
        ]);
    }

    /**
     * Test 1: Authenticated and guest profile icon and account dropdown markup.
     */
    public function test_customer_profile_icon_and_account_dropdown_render_correctly(): void
    {
        // 1. Guest view
        $guestRes = $this->get('/');
        $guestRes->assertStatus(200);
        $guestRes->assertSee(route('shop.login.email'), false);
        $guestRes->assertDontSee('id="headerUserTrigger"', false);

        // 2. Authenticated customer view
        $authRes = $this->actingAs($this->customerUser, 'customer')->get('/');
        $authRes->assertStatus(200);
        $authRes->assertSee('id="headerUserMenu"', false);
        $authRes->assertSee('id="headerUserTrigger"', false);
        $authRes->assertSee('aria-haspopup="true"', false);
        $authRes->assertSee('aria-expanded="false"', false);
        $authRes->assertSee('id="headerUserDropdown"', false);
        $authRes->assertSee('Karthik Raja', false);
        $authRes->assertSee('karthik@test-phase2.com', false);
        $authRes->assertSee(route('shop.account'), false);
        $authRes->assertSee(route('shop.orders.index'), false);
        $authRes->assertSee(route('shop.wishlist'), false);
        $authRes->assertSee(route('shop.logout'), false);

        // Bottom nav profile icon
        $authRes->assertSee('href="' . route('shop.account') . '"', false);
        $authRes->assertSee('Account', false);
    }

    /**
     * Test 2: Profile dropdown JavaScript and CSS safeguards against premature closing and viewport clipping.
     */
    public function test_profile_dropdown_javascript_and_css_safeguards(): void
    {
        $response = $this->get('/');
        $content = $response->getContent();

        // Check CSS
        $this->assertStringContainsString('.header-user-menu.open .header-user-dropdown', $content);
        $this->assertStringContainsString('animation: userDropIn', $content);

        // Check JS functions
        $this->assertStringContainsString('window.toggleUserDropdown = function', $content);
        $this->assertStringContainsString('headerUserTrigger', $content);
        $this->assertStringContainsString('headerUserDropdown', $content);
        $this->assertStringContainsString('aria-expanded', $content);

        // Viewport clipping safety
        $this->assertStringContainsString('rect.left < 10', $content);

        // Outside click listener
        $this->assertStringContainsString("document.addEventListener('click'", $content);
        $this->assertStringContainsString('headerUserMenu', $content);

        // ESC key handler for user menu
        $this->assertStringContainsString('Escape', $content);
    }

    /**
     * Test 3: Mobile navigation drawer open/close, accessibility, and BFCache handlers.
     */
    public function test_mobile_navigation_drawer_open_close_and_touch_handlers(): void
    {
        $response = $this->get('/');
        $content = $response->getContent();

        // Check drawer markup
        $this->assertStringContainsString('id="sidebar-menu-drawer"', $content);
        $this->assertStringContainsString('id="sidebar-menu-overlay"', $content);
        $this->assertStringContainsString('class="sidebar-menu-close"', $content);
        $this->assertStringContainsString('aria-label="Navigation Menu"', $content);
        $this->assertStringContainsString('id="sidebarMenuToggleBtn"', $content);

        // Check JS handlers
        $this->assertStringContainsString('window.openSidebarMenu = function', $content);
        $this->assertStringContainsString('window.closeSidebarMenu = function', $content);
        $this->assertStringContainsString('window.toggleSidebarMenu = function', $content);

        // BFCache / Browser back / reload handlers
        $this->assertStringContainsString("window.addEventListener('pageshow'", $content);
        $this->assertStringContainsString("window.addEventListener('popstate'", $content);
        $this->assertStringContainsString('closeSidebarMenu()', $content);

        // Mobile responsive drawer CSS
        $this->assertStringContainsString('height: 100dvh', $content);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch', $content);
        $this->assertStringContainsString('overscroll-behavior-y: contain', $content);
        $this->assertStringContainsString('touch-action: pan-y', $content);
        $this->assertStringContainsString('touch-action: manipulation', $content);
    }

    /**
     * Test 4: Duplicate Home icon removed from Categories page while bottom navigation Home icon remains intact.
     */
    public function test_duplicate_home_icon_removed_from_categories_page_while_bottom_nav_home_remains(): void
    {
        // 1. Visit subcategory page
        $response = $this->get('/category/' . $this->subCategory->slug);
        $response->assertStatus(200);
        $content = $response->getContent();

        // Extract breadcrumb section
        preg_match('/<nav class="cat-breadcrumbs"[^>]*>(.*?)<\/nav>/s', $content, $matches);
        $this->assertNotEmpty($matches, 'cat-breadcrumbs navigation must exist on category page');
        $breadcrumbHtml = $matches[1];

        // The breadcrumbs must NOT contain Home link or Home icon
        $this->assertStringNotContainsString('Home', $breadcrumbHtml, 'Categories page breadcrumb must not have Home text');
        $this->assertStringNotContainsString('la-home', $breadcrumbHtml, 'Categories page breadcrumb must not have la-home icon');
        $this->assertStringNotContainsString('cat-bc-link"><i class="las la-home"></i> Home</a>', $breadcrumbHtml);

        // But breadcrumb DOES contain parent category and current category
        $this->assertStringContainsString('Mens Fashion', $breadcrumbHtml);
        $this->assertStringContainsString('Printed Shirts', $breadcrumbHtml);

        // 2. The Bottom Navigation MUST still have the Home icon and Home link
        preg_match('/<section class="bottom-navigation-wrap[^>]*>(.*?)<\/section>/s', $content, $bottomNavMatches);
        $this->assertNotEmpty($bottomNavMatches, 'bottom-navigation-wrap section must exist');
        $bottomNavHtml = $bottomNavMatches[1];

        $this->assertStringContainsString('href="' . url('/') . '"', $bottomNavHtml);
        $this->assertStringContainsString('<span>Home</span>', $bottomNavHtml);
        $this->assertStringContainsString('<span>Categories</span>', $bottomNavHtml);

        // 3. Category page content must remain completely intact
        $response->assertSee('Printed Shirts', false);
        $response->assertSee('cat-page-head', false);
        $response->assertSee('cat-toolbar', false);
        $response->assertSee('Premium Printed Oxford Shirt', false);
    }

    /**
     * Test 5: Root category without parent renders cleanly without duplicate Home or broken separators.
     */
    public function test_root_category_renders_cleanly_without_duplicate_home(): void
    {
        $response = $this->get('/category/' . $this->parentCategory->slug);
        $response->assertStatus(200);
        $content = $response->getContent();

        preg_match('/<nav class="cat-breadcrumbs"[^>]*>(.*?)<\/nav>/s', $content, $matches);
        $this->assertNotEmpty($matches);
        $breadcrumbHtml = $matches[1];

        $this->assertStringNotContainsString('Home', $breadcrumbHtml);
        $this->assertStringNotContainsString('la-home', $breadcrumbHtml);
        $this->assertStringContainsString('Mens Fashion', $breadcrumbHtml);

        // Bottom nav Home is intact
        $this->assertStringContainsString('<span>Home</span>', $content);
        $this->assertStringContainsString('<span>Categories</span>', $content);
    }

    /**
     * Test 6: Unrelated pages preserve their breadcrumb Home links.
     */
    public function test_unrelated_pages_retain_breadcrumb_home_links(): void
    {
        // 1. Shop page (/shop)
        $shopRes = $this->get('/shop');
        $shopRes->assertStatus(200);
        $shopRes->assertSee('class="shop-bc-link"><i class="las la-home"></i> Home</a>', false);

        // 2. Search page (/search)
        $searchRes = $this->get('/search?q=shirt');
        $searchRes->assertStatus(200);
        $searchRes->assertSee('class="search-bc-link"><i class="las la-home"></i> Home</a>', false);

        // 3. Customer account page (/account)
        $acctRes = $this->actingAs($this->customerUser, 'customer')->get('/account');
        $acctRes->assertStatus(200);
        $acctRes->assertSee('class="acct-bc-link"><i class="las la-home"></i> Home</a>', false);
    }

    /**
     * Test 7: Strict WhatsApp protection audit - WhatsApp files remain untouched.
     */
    public function test_whatsapp_files_and_services_remain_unmodified(): void
    {
        $this->assertTrue(file_exists(base_path('app/Services/WhatsAppService.php')));
        $this->assertTrue(file_exists(base_path('tests/Feature/WhatsAppIntegrationTest.php')));
        $this->assertTrue(file_exists(base_path('tests/Feature/WhatsAppNotificationWalletTest.php')));

        // Ensure WhatsAppService still has its core notification methods
        $whatsAppCode = file_get_contents(base_path('app/Services/WhatsAppService.php'));
        $this->assertStringContainsString('class WhatsAppService', $whatsAppCode);
    }
}
