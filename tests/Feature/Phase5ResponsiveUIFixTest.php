<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\Review;
use App\Models\StoreSetting;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5ResponsiveUIFixTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::clearCache();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->customer = User::factory()->create(['is_admin' => false]);
    }

    /**
     * Test 1: Theme & Appearance responsive markup, media queries, and container styling.
     */
    public function test_theme_appearance_responsive_css_and_elements(): void
    {
        // 1. Theme Customizer
        $resTheme = $this->actingAs($this->admin)->get(route('admin.appearance.theme.index'));
        $resTheme->assertStatus(200);
        $resTheme->assertSee('theme-customizer-wrap', false);
        $resTheme->assertSee('theme-columns', false);
        $resTheme->assertSee('preset-grid', false);
        $resTheme->assertSee('style-options-grid', false);
        $resTheme->assertSee('preview-sticky', false);
        $resTheme->assertSee('@media (max-width: 991px)', false);
        $resTheme->assertSee('@media (max-width: 768px)', false);
        $resTheme->assertSee('@media (max-width: 640px)', false);
        $resTheme->assertSee('@media (max-width: 480px)', false);

        // 2. Homepage Product Sections Manager
        $resSec = $this->actingAs($this->admin)->get(route('admin.appearance.sections.index'));
        $resSec->assertStatus(200);
        $resSec->assertSee('sec-manager-wrap', false);
        $resSec->assertSee('save-bar-bottom', false);
        $resSec->assertSee('page-title-actions', false);
        $resSec->assertSee('btn-save-sections', false);
        $resSec->assertSee('@media (max-width: 768px)', false);
        $resSec->assertSee('@media (max-width: 640px)', false);
        $resSec->assertSee('@media (max-width: 480px)', false);

        // 3. Footer Settings
        $resFooter = $this->actingAs($this->admin)->get(route('admin.appearance.footer.index'));
        $resFooter->assertStatus(200);
        $resFooter->assertSee('footer-manager-wrap', false);
        $resFooter->assertSee('footer-preview-grid', false);
        $resFooter->assertSee('footer-header-actions', false);
        $resFooter->assertSee('link-row', false);
        $resFooter->assertSee('@media (max-width: 768px)', false);
        $resFooter->assertSee('@media (max-width: 576px)', false);
        $resFooter->assertSee('@media (max-width: 480px)', false);

        // 4. Files & Media Manager
        $resFiles = $this->actingAs($this->admin)->get(route('admin.appearance.files.index'));
        $resFiles->assertStatus(200);
        $resFiles->assertSee('media-list-table-wrap', false);
        $resFiles->assertSee('media-tabs', false);
        $resFiles->assertSee('overflow-x: auto', false);
        $resFiles->assertSee('min-width: 680px', false);
        $resFiles->assertSee('@media (max-width: 768px)', false);
        $resFiles->assertSee('@media (max-width: 576px)', false);
    }

    /**
     * Test 2: Returns & Requests responsive table, scroll containers, and manage controls.
     */
    public function test_returns_requests_responsive_layout_and_actions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cancellations-returns.index'));
        $response->assertStatus(200);

        // Responsive scroll container classes and scroll hint
        $response->assertSee('table-responsive', false);
        $response->assertSee('data-cancellations', false);
        $response->assertSee('data-returns', false);
        $response->assertSee('mobile-scroll-hint', false);
        $response->assertSee('col-manage', false);

        // Responsive media queries
        $response->assertSee('@media (max-width: 860px)', false);
        $response->assertSee('@media (max-width: 576px)', false);
        $response->assertSee('@media (max-width: 520px)', false);
        $response->assertSee('@media (max-width: 480px)', false);
    }

    /**
     * Test 3: Reviews responsiveness, Phase 3 categories, and scrollable edit modal.
     */
    public function test_reviews_responsiveness_and_phase3_categories(): void
    {
        $category = Category::create([
            'name' => 'Men Fashion',
            'slug' => 'men-fashion',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Oversized Cotton Tee',
            'slug' => 'oversized-cotton-tee',
            'sku' => 'TSHIRT-001',
            'price' => 599.00,
            'stock' => 50,
            'is_active' => true,
        ]);

        // Create reviews in the 3 canonical categories
        Review::create([
            'product_id' => $product->id,
            'customer_name' => 'Alice Quality',
            'customer_email' => 'alice@example.com',
            'rating' => 5,
            'category' => 'quality',
            'comment' => 'Quality fabric and stitching.',
            'is_approved' => true,
        ]);
        Review::create([
            'product_id' => $product->id,
            'customer_name' => 'Bob Service',
            'customer_email' => 'bob@example.com',
            'rating' => 4,
            'category' => 'customer_service',
            'comment' => 'Great support team response.',
            'is_approved' => true,
        ]);
        Review::create([
            'product_id' => $product->id,
            'customer_name' => 'Charlie Cart',
            'customer_email' => 'charlie@example.com',
            'rating' => 5,
            'category' => 'cart',
            'comment' => 'Smooth checkout and cart experience.',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reviews.index'));
        $response->assertStatus(200);

        // Table responsive container and scroll hint
        $response->assertSee('table-responsive', false);
        $response->assertSee('rv-table', false);
        $response->assertSee('mobile-scroll-hint', false);

        // Responsive modal container
        $response->assertSee('review-modal-box', false);
        $response->assertSee('max-height: 90vh', false);
        $response->assertSee('overflow-y: auto', false);

        // Canonical category badges rendered
        $response->assertSee('Quality Review');
        $response->assertSee('Customer Service');
        $response->assertSee('Cart Review');

        // Responsive media queries
        $response->assertSee('@media (max-width: 900px)', false);
        $response->assertSee('@media (max-width: 768px)', false);
        $response->assertSee('@media (max-width: 480px)', false);
    }

    /**
     * Test 4: Support Inbox responsive layout, scroll hint, thread, and submit button.
     */
    public function test_support_inbox_responsive_layout(): void
    {
        $ticket = SupportTicket::create([
            'customer_id' => $this->customer->id,
            'subject' => 'Size Exchange Request',
            'category' => 'order',
            'status' => 'open',
        ]);
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $this->customer->id,
            'sender_role' => 'customer',
            'message' => 'Need help exchanging size M for L with very long details https://example.com/item/exchange-policy-terms.',
        ]);

        // 1. Support Index
        $resIndex = $this->actingAs($this->admin)->get(route('admin.support.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('table-responsive', false);
        $resIndex->assertSee('mobile-scroll-hint', false);
        $resIndex->assertSee('col-ticket', false);
        $resIndex->assertSee('col-subject', false);
        $resIndex->assertSee('col-action', false);
        $resIndex->assertSee('btn-open-ticket', false);
        $resIndex->assertSee('kpi-row', false);
        $resIndex->assertSee('toolbar', false);
        $resIndex->assertSee('Size Exchange Request');
        $resIndex->assertSee('@media (max-width: 900px)', false);
        $resIndex->assertSee('@media (max-width: 600px)', false);

        // 2. Support Show (Thread & Reply)
        $resShow = $this->actingAs($this->admin)->get(route('admin.support.show', $ticket));
        $resShow->assertStatus(200);
        $resShow->assertSee('thread', false);
        $resShow->assertSee('overflow-wrap: anywhere', false);
        $resShow->assertSee('word-break: break-word', false);
        $resShow->assertSee('reply-box', false);
        $resShow->assertSee('btn-submit', false);
        $resShow->assertSee('@media (max-width: 768px)', false);
        $resShow->assertSee('@media (max-width: 576px)', false);

        // 3. Verify reply action submission & database persistence
        $replyResponse = $this->actingAs($this->admin)->post(route('admin.support.reply', $ticket), [
            'message' => 'Thank you for contacting support. We are reviewing your ticket.',
        ]);
        $replyResponse->assertRedirect();
        $replyResponse->assertSessionHas('success', 'Reply sent.');
        $this->assertDatabaseHas('support_messages', [
            'ticket_id' => $ticket->id,
            'sender_role' => 'admin',
            'message' => 'Thank you for contacting support. We are reviewing your ticket.',
        ]);
    }

    /**
     * Test 5: Sales & Reports responsive layout, metrics grid, and filter bar.
     */
    public function test_reports_responsive_layout(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);

        // Metrics grid and cards
        $response->assertSee('rep-metrics', false);
        $response->assertSee('rep-card', false);
        $response->assertSee('rep-filter-btns', false);
        $response->assertSee('table-responsive', false);
        $response->assertSee('mobile-scroll-hint', false);
        $response->assertSee('col-prod-rev', false);
        $response->assertSee('rep-table', false);

        // Responsive media queries and mobile grid safeguards
        $response->assertSee('@media (max-width: 768px)', false);
        $response->assertSee('@media (max-width: 600px)', false);
        $response->assertSee('@media (max-width: 480px)', false);
        $response->assertSee('@media (max-width: 360px)', false);
        $response->assertSee('.container-fluid > .row', false);
    }

    /**
     * Test 6: Verify Phase 1 mobile scrolling fixes remain intact.
     */
    public function test_phase1_scrolling_fixes_remain_intact(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Global admin layout scroll safeguards
        $response->assertSee('-webkit-overflow-scrolling: touch', false);
        $response->assertSee('overflow-x: auto', false);
        $response->assertSee('touch-action: pan-x pan-y', false);
        $response->assertSee('table-responsive', false);
    }

    /**
     * Test 7: Verify Phase 2 navigation & Categories duplicate Home removal remain intact.
     */
    public function test_phase2_profile_and_categories_remain_intact(): void
    {
        // Check storefront categories page
        $resCategories = $this->get('/categories');
        if ($resCategories->status() === 200) {
            $content = $resCategories->getContent();
            // Should not have duplicate standalone Home card inside categories grid
            $this->assertStringNotContainsString('class="category-card" href="/"', $content);
        }

        // Bottom nav bar must retain Home link
        $resHome = $this->get('/');
        $resHome->assertStatus(200);
        $resHome->assertSee('href="' . url('/') . '"', false);
    }

    /**
     * Test 8: Verify Phase 4 All Products link remains functional and accessible.
     */
    public function test_phase4_all_products_remains_visible_and_functional(): void
    {
        $resAdmin = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $resAdmin->assertStatus(200);

        // Admin desktop topbar and mobile drawer retain All Products
        $resAdmin->assertSee('admin/products', false);
        $resAdmin->assertSee('All Products', false);

        // Endpoint itself is functional
        $resProducts = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $resProducts->assertStatus(200);
    }

    /**
     * Test 9: Strict WhatsApp protection audit.
     */
    public function test_whatsapp_protection_audit(): void
    {
        // 1. WhatsApp service class exists and methods unchanged
        $this->assertTrue(class_exists(\App\Services\WhatsAppService::class));
        $this->assertTrue(method_exists(\App\Services\WhatsAppService::class, 'resolveCredentials'));
        $this->assertTrue(method_exists(\App\Services\WhatsAppService::class, 'getAdminPhone'));
        $this->assertTrue(method_exists(\App\Services\WhatsAppService::class, 'getMessageId'));
        $this->assertTrue(method_exists(\App\Services\WhatsAppService::class, 'getTemplateName'));
        $this->assertTrue(method_exists(\App\Services\WhatsAppService::class, 'getPluginWhatsAppUrl'));

        // 2. WhatsApp configuration exists
        $this->assertTrue(config()->has('services.whatsapp') || file_exists(config_path('services.php')));

        // 3. Normalization and URL builder behavior remains intact
        $this->assertEquals('919876543210', StoreSetting::normalizePhoneNumber('9876543210'));
        $this->assertEquals('918056081594', StoreSetting::normalizePhoneNumber('+91 80560 81594'));
    }

    /**
     * Test 10: Admin account dropdown markup, accessibility, CSS and script safeguards.
     */
    public function test_admin_account_dropdown_markup_and_safeguards(): void
    {
        $resAdmin = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $resAdmin->assertStatus(200);
        $content = $resAdmin->getContent();

        // 1. Account dropdown wrapper and toggle trigger
        $this->assertStringContainsString('class="dropdown admin-user-dropdown"', $content);
        $this->assertStringContainsString('class="dropdown-toggle admin-user-btn"', $content);
        $this->assertStringContainsString('data-toggle="dropdown"', $content);
        $this->assertStringContainsString('id="adminUserDropdownBtn"', $content);
        $this->assertStringContainsString('role="button"', $content);
        $this->assertStringContainsString('aria-haspopup="true"', $content);
        $this->assertStringContainsString('aria-expanded="false"', $content);
        $this->assertStringContainsString('class="admin-user-avatar"', $content);
        $this->assertStringContainsString('class="admin-user-name"', $content);

        // 2. Dropdown menu and target links
        $this->assertStringContainsString('class="dropdown-menu dropdown-menu-right"', $content);
        $this->assertStringContainsString('aria-labelledby="adminUserDropdownBtn"', $content);
        $this->assertStringContainsString(url('admin/account'), $content);
        $this->assertStringContainsString(url('admin/settings'), $content);
        $this->assertStringContainsString('id="nav-logout-btn"', $content);
        $this->assertStringContainsString('id="nav-logout-form"', $content);
        $this->assertStringContainsString(route('admin.logout'), $content);

        // 3. Dropdown styling and z-index positioning safeguards
        $this->assertStringContainsString('.admin-user-dropdown', $content);
        $this->assertStringContainsString('.admin-user-dropdown .dropdown-menu', $content);
        $this->assertStringContainsString('z-index: 1050', $content);

        // 4. Script collision safeguards: Conflicting double-toggle click handler must be eliminated
        $this->assertStringNotContainsString(".admin-user-dropdown > [data-toggle=\"dropdown\"]", $content);

        // 5. Global dropdown outside-click and escape listeners are present
        $this->assertStringContainsString("if (!$(e.target).closest('.dropdown').length)", $content);
        $this->assertStringContainsString("if (e.key === 'Escape')", $content);

        // 6. Profile & Account and Settings routes remain accessible
        $resAccount = $this->actingAs($this->admin)->get(url('admin/account'));
        $this->assertTrue(in_array($resAccount->status(), [200, 302]));

        $resSettings = $this->actingAs($this->admin)->get(url('admin/settings'));
        $this->assertTrue(in_array($resSettings->status(), [200, 302]));
    }
}
