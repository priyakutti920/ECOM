<?php

namespace Tests\Feature;

use App\Models\Bonus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1ScopeCompletionAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::clearCache();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /**
     * 1. Theme and Appearance Customization (admin/appearance/theme)
     */
    public function test_theme_and_appearance_customization_renders_responsive_css_and_containers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.appearance.theme.index'));
        $response->assertStatus(200);

        // Responsive classes, CSS media queries and wrapping
        $response->assertSee('theme-header-bar', false);
        $response->assertSee('theme-header-actions', false);
        $response->assertSee('preset-grid', false);
        $response->assertSee('style-options-grid', false);
        $response->assertSee('@media (max-width: 640px)', false);
        $response->assertSee('@media (max-width: 480px)', false);
    }

    /**
     * 2. Return and Request Management (admin/cancellations-returns)
     */
    public function test_cancellations_and_returns_renders_responsive_containers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cancellations-returns.index'));
        $response->assertStatus(200);

        $response->assertSee('table-responsive', false);
        $response->assertSee('mobile-scroll-hint', false);
        $response->assertSee('@media (max-width: 576px)', false);
    }

    /**
     * 3. Product Reviews and Ratings (admin/reviews)
     */
    public function test_product_reviews_renders_responsive_filters_and_table_container(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reviews.index'));
        $response->assertStatus(200);

        $response->assertSee('table-responsive', false);
        $response->assertSee('rv-table', false);
        $response->assertSee('min-width: 900px', false);
        $response->assertSee('mobile-scroll-hint', false);
        $response->assertSee('@media (max-width: 768px)', false);
    }

    /**
     * 4. Support Inbox (admin/support) and Detail (admin/support/{ticket})
     */
    public function test_support_inbox_and_ticket_detail_render_responsive_containers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.support.index'));
        $response->assertStatus(200);

        $response->assertSee('table-responsive', false);
        $response->assertSee('min-width: 720px', false);
        $response->assertSee('mobile-scroll-hint', false);
        $response->assertSee('@media (max-width: 900px)', false);
        $response->assertSee('@media (max-width: 600px)', false);

        // Test ticket detail page
        $customer = User::factory()->create(['is_admin' => false]);
        $ticket = SupportTicket::create([
            'customer_id' => $customer->id,
            'subject' => 'Need help with delivery',
            'category' => 'delivery',
            'status' => 'open',
        ]);

        $detailResponse = $this->actingAs($this->admin)->get(route('admin.support.show', $ticket));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('@media (max-width: 576px)', false);
    }

    /**
     * 5. Sales & Performance Reports (admin/reports)
     */
    public function test_reports_page_renders_responsive_filters_and_tables(): void
    {
        $response = $this->actingAs($this->admin)->get(url('admin/reports'));
        $response->assertStatus(200);

        $response->assertSee('table-responsive', false);
        $response->assertSee('rep-filter-btns', false);
        $response->assertSee('min-width: 440px', false);
        $response->assertSee('@media (max-width: 600px)', false);
        $response->assertSee('@media (max-width: 480px)', false);
    }

    /**
     * 6. More Pages: Logs, Coupons, Tasks, Broadcasts, Bonuses
     */
    public function test_more_pages_render_responsive_containers_and_tables(): void
    {
        // Activity logs
        $logsRes = $this->actingAs($this->admin)->get(url('admin/logs'));
        $logsRes->assertStatus(200);
        $logsRes->assertSee('min-width: 720px', false);

        // Coupons
        $couponsRes = $this->actingAs($this->admin)->get(url('admin/coupons'));
        $couponsRes->assertStatus(200);
        $couponsRes->assertSee('table-responsive', false);
        $couponsRes->assertSee('min-width: 760px', false);
        $couponsRes->assertSee('@media (max-width: 600px)', false);

        // Tasks
        $tasksRes = $this->actingAs($this->admin)->get(url('admin/tasks'));
        $tasksRes->assertStatus(200);
        $tasksRes->assertSee('@media (max-width: 480px)', false);

        // Broadcasts
        $bcRes = $this->actingAs($this->admin)->get(url('admin/broadcasts'));
        $bcRes->assertStatus(200);
        $bcRes->assertSee('@media (max-width: 576px)', false);

        // Bonuses
        $bonusRes = $this->actingAs($this->admin)->get(url('admin/bonuses'));
        $bonusRes->assertStatus(200);
        $bonusRes->assertSee('@media (max-width: 480px)', false);
    }

    /**
     * 7. Inventory & Payments & Users & Navigation & Orders
     */
    public function test_additional_admin_pages_render_responsive_containers(): void
    {
        // Inventory
        $invRes = $this->actingAs($this->admin)->get(route('admin.inventory.index'));
        $invRes->assertStatus(200);
        $invRes->assertSee('table-responsive', false);
        $invRes->assertSee('min-width: 820px', false);
        $invRes->assertSee('@media (max-width: 768px)', false);

        // Payments
        $payRes = $this->actingAs($this->admin)->get(route('admin.payments.index'));
        $payRes->assertStatus(200);
        $payRes->assertSee('table-responsive', false);
        $payRes->assertSee('min-width: 860px', false);
        $payRes->assertSee('@media (max-width: 768px)', false);

        // Users
        $usersRes = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $usersRes->assertStatus(200);
        $usersRes->assertSee('table-responsive', false);
        $usersRes->assertSee('min-width: 860px', false);
        $usersRes->assertSee('@media (max-width: 768px)', false);

        // Navigation
        $navRes = $this->actingAs($this->admin)->get(route('admin.navigation.index'));
        $navRes->assertStatus(200);
        $navRes->assertSee('@media (max-width: 600px)', false);

        // Orders
        $ordersRes = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $ordersRes->assertStatus(200);
        $ordersRes->assertSee('table-responsive', false);
        $ordersRes->assertSee('min-width: 980px', false);
        $ordersRes->assertSee('@media (max-width: 768px)', false);

        // Global Layout
        $ordersRes->assertSee('admin-content', false);
        $ordersRes->assertSee('@media (max-width: 576px)', false);
    }
}
