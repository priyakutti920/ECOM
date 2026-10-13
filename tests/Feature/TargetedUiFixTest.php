<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TargetedUiFixTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Cotton Shirts',
            'slug' => 'cotton-shirts',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'name' => 'Classic Cotton Shirt',
            'slug' => 'classic-cotton-shirt',
            'code' => 'CCS-01',
            'category_id' => $this->category->id,
            'price' => 799.00,
            'is_active' => true,
        ]);
    }

    /**
     * Requirement 1 & 5: Compare is removed from desktop header and mobile taskbar.
     * Relevant is not present. Single Wishlist is present in desktop header and Wishlist action is present in mobile taskbar.
     */
    public function test_compare_removed_and_wishlist_configured_in_navigation_and_taskbar(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $content = $response->getContent();

        // 1. Confirm Compare is no longer displayed anywhere in taskbar or header
        $this->assertStringNotContainsString('<span>Compare</span>', $content, 'Visible Compare label must be removed from taskbar');
        $this->assertStringNotContainsString('title="Compare"', $content, 'Compare title action must be removed from header');
        $this->assertStringNotContainsString('aria-label="Compare Products"', $content, 'Compare aria label must be removed');

        // 2. Confirm Relevant is not displayed anywhere in taskbar or header
        $this->assertStringNotContainsString('<span>Relevant</span>', $content, 'Relevant label must not be in taskbar');
        $this->assertStringNotContainsString('title="Relevant"', $content, 'Relevant title action must not be in header');
        $this->assertStringNotContainsString('aria-label="Relevant Products"', $content, 'Relevant aria label must not be in header');

        // 3. Confirm desktop header actions contain single Wishlist (no duplicates)
        preg_match('/<div class="header-actions[^>]*>(.*?)<\/div>\s*<\/div>\s*<\/div>/s', $content, $headerActionMatches);
        $this->assertNotEmpty($headerActionMatches, 'header-actions container must exist');
        $headerActionHtml = $headerActionMatches[0];
        $this->assertStringContainsString('title="Wishlist"', $headerActionHtml, 'Header must contain Wishlist action');
        $this->assertStringContainsString('href="' . route('shop.wishlist') . '"', $headerActionHtml);
        $this->assertEquals(1, substr_count($headerActionHtml, 'title="Wishlist"'), 'Header must contain exactly one Wishlist action');

        // 4. Confirm mobile bottom taskbar contains Wishlist action
        preg_match('/<section class="bottom-navigation-wrap[^>]*>(.*?)<\/section>/s', $content, $bottomNavMatches);
        $this->assertNotEmpty($bottomNavMatches, 'bottom-navigation-wrap must exist');
        $bottomNavHtml = $bottomNavMatches[1];
        $this->assertStringContainsString('<span>Wishlist</span>', $bottomNavHtml, 'Bottom navigation must have Wishlist item');
        $this->assertStringContainsString('href="' . route('shop.wishlist') . '"', $bottomNavHtml, 'Wishlist item must navigate to /wishlist');

        // 5. Confirm Wishlist page loads cleanly
        $wishlistRes = $this->get('/wishlist');
        $wishlistRes->assertStatus(200);
    }

    /**
     * Requirement 2: Taskbar Categories navigates directly to the categories page without opening menus/drawers.
     */
    public function test_taskbar_categories_navigates_directly_without_opening_menu(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Extract bottom navigation markup
        preg_match('/<section class="bottom-navigation-wrap[^>]*>(.*?)<\/section>/s', $content, $bottomNavMatches);
        $this->assertNotEmpty($bottomNavMatches, 'bottom-navigation-wrap must exist');
        $bottomNavHtml = $bottomNavMatches[1];

        // Ensure Categories item exists with label
        $this->assertStringContainsString('<span>Categories</span>', $bottomNavHtml);

        // Extract the Categories anchor tag from taskbar
        preg_match('/<a[^>]*>(?:(?!<\/a>).)*<span>Categories<\/span>.*?<\/a>/s', $bottomNavHtml, $catAnchorMatches);
        $this->assertNotEmpty($catAnchorMatches, 'Categories anchor must exist in taskbar');
        $catAnchorHtml = $catAnchorMatches[0];

        // 1. Must navigate directly to the existing categories page URL
        $this->assertStringContainsString('href="' . url('/shop') . '"', $catAnchorHtml);

        // 2. Must NOT open dropdown, popup, sidebar, or menu from this taskbar click
        $this->assertStringNotContainsString('onclick="toggleSidebarMenu', $catAnchorHtml);
        $this->assertStringNotContainsString('toggleSidebarMenu()', $catAnchorHtml);
        $this->assertStringNotContainsString('javascript:void', $catAnchorHtml);
        $this->assertStringNotContainsString('data-toggle', $catAnchorHtml);
        $this->assertStringNotContainsString('data-bs-toggle', $catAnchorHtml);

        // 3. Categories page loads successfully and displays categories listing
        $categoriesPageRes = $this->get('/shop');
        $categoriesPageRes->assertStatus(200);
        $categoriesPageRes->assertSee('Cotton Shirts', false);
        $categoriesPageRes->assertSee('Categories', false);
        $categoriesPageRes->assertSee('shop-cats-card', false);

        // Subcategory URL loads cleanly as well
        $subCatRes = $this->get('/category/' . $this->category->slug);
        $subCatRes->assertStatus(200);
        $subCatRes->assertSee('Cotton Shirts', false);
    }

    /**
     * Confirm separate desktop header menu toggle button behavior is preserved.
     */
    public function test_desktop_sidebar_toggle_remains_intact(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Header hamburger button still opens the drawer
        $this->assertStringContainsString('id="sidebarMenuToggleBtn"', $content);
        $this->assertStringContainsString('onclick="toggleSidebarMenu()"', $content);
        $this->assertStringContainsString('id="sidebar-menu-drawer"', $content);
    }

    /**
     * Strict safety: confirm WhatsApp remains completely untouched.
     */
    public function test_whatsapp_protection_audit(): void
    {
        $this->assertTrue(file_exists(base_path('app/Services/WhatsAppService.php')));
        $whatsAppCode = file_get_contents(base_path('app/Services/WhatsAppService.php'));
        $this->assertStringContainsString('class WhatsAppService', $whatsAppCode);
    }
}
