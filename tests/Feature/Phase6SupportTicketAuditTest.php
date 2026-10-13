<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6SupportTicketAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::clearCache();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->customer = User::factory()->create(['is_admin' => false]);
        $this->otherCustomer = User::factory()->create(['is_admin' => false]);
    }

    /**
     * Test 1: Customer Get Help navigation and link visibility across desktop & mobile.
     */
    public function test_customer_get_help_navigation_and_links_visibility(): void
    {
        // 1. Account page navigation links
        $resAccount = $this->actingAs($this->customer, 'customer')->get(route('shop.account'));
        $resAccount->assertStatus(200);
        $resAccount->assertSee(route('shop.support.index'), false);
        $resAccount->assertSee(route('shop.help'), false);
        $resAccount->assertSee('Support Tickets', false);
        $resAccount->assertSee('Help &amp; FAQ', false);

        // 2. Customer header dropdown navigation links
        $resHome = $this->actingAs($this->customer, 'customer')->get(url('/'));
        $resHome->assertStatus(200);
        $resHome->assertSee(route('shop.support.index'), false);
        $resHome->assertSee(route('shop.help'), false);

        // 3. Mobile sidebar drawer navigation links
        $resHome->assertSee('sidebar-menu-drawer', false);
        $resHome->assertSee('Help &amp; Support', false);

        // 4. Order detail page "Get Help" button with prefilled order parameter
        $product = Product::create([
            'name'         => 'Test T-Shirt',
            'slug'         => 'test-t-shirt-help',
            'code'         => 'TSHIRT-HELP',
            'price'        => 1200,
            'is_active'    => true,
            'stock_status' => 'in_stock',
        ]);
        $order = Order::create([
            'order_code'             => 'ORD-HELP-1001',
            'customer_id'            => $this->customer->id,
            'status'                 => 'confirmed',
            'payment_status'         => 'paid',
            'payment_method'         => 'upi',
            'subtotal'               => 1200,
            'total'                  => 1200,
            'addr_full_name'         => 'Test Customer',
            'addr_mobile_primary'    => '9876543210',
            'addr_line_1'            => '123 Main St',
            'addr_city'              => 'Tirunelveli',
            'addr_state'             => 'Tamil Nadu',
            'addr_pincode'           => '627001',
            'addr_type'              => 'home',
            'contact_name'           => 'Test Customer',
            'contact_mobile'         => '9876543210',
        ]);
        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'product_name'   => $product->name,
            'unit_price'     => 1200,
            'quantity'       => 1,
            'line_total'     => 1200,
        ]);

        $resOrder = $this->actingAs($this->customer, 'customer')->get(route('shop.orders.show', ['order' => $order->order_code]));
        $resOrder->assertStatus(200);
        $resOrder->assertSee(route('shop.help') . '?order=ORD-HELP-1001#newTicket', false);
        $resOrder->assertSee('Get Help', false);
    }

    /**
     * Test 2: Guest help page behavior, login prompts, and unauthorized ticket creation.
     */
    public function test_guest_help_page_behavior_and_login_prompt(): void
    {
        $resHelp = $this->get(route('shop.help'));
        $resHelp->assertStatus(200);
        $resHelp->assertSee('How can we help?', false);
        $resHelp->assertSee('Frequently asked questions', false);
        $resHelp->assertSee('Sign in to open a support ticket', false);
        $resHelp->assertSee(route('shop.login.email'), false);
        $resHelp->assertSee('to view your support tickets', false);

        // Guest trying to POST directly to ticket store endpoint is blocked
        $resPost = $this->post(route('shop.support.store'), [
            'subject'    => 'Guest inquiry',
            'category'   => 'general',
            'message'    => 'Need information on products',
        ]);
        $resPost->assertRedirect(route('shop.login.email'));
    }

    /**
     * Test 3: Support ticket creation, validation, and database storage.
     */
    public function test_customer_ticket_creation_validation_and_storage(): void
    {
        // 1. Validation failure: subject too short
        $resFail1 = $this->actingAs($this->customer, 'customer')->post(route('shop.support.store'), [
            'subject'    => 'Hi',
            'category'   => 'general',
            'message'    => 'Detailed message about inquiry.',
        ]);
        $resFail1->assertSessionHasErrors(['subject']);

        // 2. Validation failure: invalid category
        $resFail2 = $this->actingAs($this->customer, 'customer')->post(route('shop.support.store'), [
            'subject'    => 'Valid Subject',
            'category'   => 'invalid_category_xyz',
            'message'    => 'Detailed message about inquiry.',
        ]);
        $resFail2->assertSessionHasErrors(['category']);

        // 3. Validation failure: message too short
        $resFail3 = $this->actingAs($this->customer, 'customer')->post(route('shop.support.store'), [
            'subject'    => 'Valid Subject',
            'category'   => 'order',
            'message'    => 'Hi',
        ]);
        $resFail3->assertSessionHasErrors(['message']);

        // 4. Successful creation
        $resSuccess = $this->actingAs($this->customer, 'customer')->post(route('shop.support.store'), [
            'subject'    => 'Defective zipper on order item',
            'category'   => 'order',
            'order_code' => 'ORD-HELP-1001',
            'message'    => 'The jacket zipper is jammed and cannot be opened.',
        ]);

        $ticket = SupportTicket::where('subject', 'Defective zipper on order item')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($this->customer->id, $ticket->customer_id);
        $this->assertEquals('awaiting_admin', $ticket->status);
        $this->assertEquals('order', $ticket->category);
        $this->assertEquals('ORD-HELP-1001', $ticket->order_code);
        $this->assertNotNull($ticket->last_message_at);

        $this->assertDatabaseHas('support_messages', [
            'ticket_id'   => $ticket->id,
            'sender_id'   => $this->customer->id,
            'sender_role' => 'customer',
            'message'     => 'The jacket zipper is jammed and cannot be opened.',
        ]);

        $resSuccess->assertRedirect(route('shop.support.show', ['ticket' => $ticket->id]));
        $resSuccess->assertSessionHas('success');
    }

    /**
     * Test 4: Ticket list, ticket detail view, and unauthorized access handling.
     */
    public function test_customer_ticket_list_and_detail_thread_view(): void
    {
        $ticket = SupportTicket::create([
            'customer_id'     => $this->customer->id,
            'subject'         => 'Where is my invoice copy?',
            'category'        => 'payment',
            'order_code'      => 'ORD-999',
            'status'          => 'open',
            'last_message_at' => now(),
        ]);
        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => $this->customer->id,
            'sender_role' => 'customer',
            'message'     => 'Please email me my GST invoice copy.',
        ]);
        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => $this->admin->id,
            'sender_role' => 'admin',
            'message'     => 'We have attached and emailed your invoice copy.',
        ]);

        // 1. Customer ticket list
        $resIndex = $this->actingAs($this->customer, 'customer')->get(route('shop.support.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Where is my invoice copy?');
        $resIndex->assertSee('ORD-999');
        $resIndex->assertSee('status-open');

        // 2. Customer ticket detail view
        $resShow = $this->actingAs($this->customer, 'customer')->get(route('shop.support.show', ['ticket' => $ticket->id]));
        $resShow->assertStatus(200);
        $resShow->assertSee('Ticket #' . $ticket->id);
        $resShow->assertSee('Where is my invoice copy?');
        $resShow->assertSee('Please email me my GST invoice copy.');
        $resShow->assertSee('We have attached and emailed your invoice copy.');

        // 3. Unauthorized access: another customer cannot view this ticket
        $resOther = $this->actingAs($this->otherCustomer, 'customer')->get(route('shop.support.show', ['ticket' => $ticket->id]));
        $resOther->assertStatus(404);
    }

    /**
     * Test 5: Customer reply submission, ticket closure, and closed ticket safeguards.
     */
    public function test_customer_reply_submission_and_closing_ticket(): void
    {
        $ticket = SupportTicket::create([
            'customer_id'     => $this->customer->id,
            'subject'         => 'Exchange size request',
            'category'        => 'return',
            'status'          => 'awaiting_customer',
            'last_message_at' => now(),
        ]);
        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => $this->customer->id,
            'sender_role' => 'customer',
            'message'     => 'Need size L instead of M.',
        ]);

        // 1. Customer replies
        $resReply = $this->actingAs($this->customer, 'customer')->post(route('shop.support.reply', ['ticket' => $ticket->id]), [
            'message' => 'Thank you, size L is perfect.',
        ]);
        $resReply->assertRedirect(route('shop.support.show', ['ticket' => $ticket->id]));
        $resReply->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('awaiting_admin', $ticket->status);
        $this->assertDatabaseHas('support_messages', [
            'ticket_id'   => $ticket->id,
            'sender_role' => 'customer',
            'message'     => 'Thank you, size L is perfect.',
        ]);

        // 2. Customer closes ticket
        $resClose = $this->actingAs($this->customer, 'customer')->post(route('shop.support.close', ['ticket' => $ticket->id]));
        $resClose->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
        $this->assertEquals($this->customer->id, $ticket->closed_by);

        // 3. Attempting to reply to closed ticket fails with 403
        $resClosedReply = $this->actingAs($this->customer, 'customer')->post(route('shop.support.reply', ['ticket' => $ticket->id]), [
            'message' => 'Can I still add a question?',
        ]);
        $resClosedReply->assertStatus(403);

        // 4. Re-closing already closed ticket returns info
        $resReClose = $this->actingAs($this->customer, 'customer')->post(route('shop.support.close', ['ticket' => $ticket->id]));
        $resReClose->assertSessionHas('info');
    }

    /**
     * Test 6: Admin Support Inbox access, ticket ID search, status filters, and KPIs.
     */
    public function test_admin_support_inbox_access_search_and_kpis(): void
    {
        // 1. Guest cannot access admin support
        $resGuest = $this->get(route('admin.support.index'));
        $resGuest->assertRedirect();

        // Create tickets
        $ticket1 = SupportTicket::create([
            'customer_id'     => $this->customer->id,
            'subject'         => 'Order tracking delay issue',
            'category'        => 'order',
            'order_code'      => 'ORD-TRK-777',
            'status'          => 'awaiting_admin',
            'last_message_at' => now(),
        ]);
        SupportMessage::create([
            'ticket_id'   => $ticket1->id,
            'sender_id'   => $this->customer->id,
            'sender_role' => 'customer',
            'message'     => 'My order tracking has not updated for 3 days.',
        ]);

        $ticket2 = SupportTicket::create([
            'customer_id'     => $this->otherCustomer->id,
            'subject'         => 'Refund credited confirmation',
            'category'        => 'payment',
            'status'          => 'closed',
            'last_message_at' => now(),
        ]);

        // 2. Admin inbox renders with KPIs and responsive table
        $resInbox = $this->actingAs($this->admin)->get(route('admin.support.index'));
        $resInbox->assertStatus(200);
        $resInbox->assertSee('Support Inbox');
        $resInbox->assertSee('table-responsive');
        $resInbox->assertSee('mobile-scroll-hint');
        $resInbox->assertSee('Order tracking delay issue');
        $resInbox->assertSee('Refund credited confirmation');

        // 3. Search by numeric ticket ID
        $resSearchId = $this->actingAs($this->admin)->get(route('admin.support.index', ['q' => (string) $ticket1->id]));
        $resSearchId->assertStatus(200);
        $resSearchId->assertSee('Order tracking delay issue');
        $resSearchId->assertDontSee('Refund credited confirmation');

        // 4. Search by ticket ID with '#' prefix
        $resSearchHash = $this->actingAs($this->admin)->get(route('admin.support.index', ['q' => '#' . $ticket1->id]));
        $resSearchHash->assertStatus(200);
        $resSearchHash->assertSee('Order tracking delay issue');
        $resSearchHash->assertDontSee('Refund credited confirmation');

        // 5. Filter by status
        $resFilterClosed = $this->actingAs($this->admin)->get(route('admin.support.index', ['status' => 'closed']));
        $resFilterClosed->assertStatus(200);
        $resFilterClosed->assertSee('Refund credited confirmation');
        $resFilterClosed->assertDontSee('Order tracking delay issue');
    }

    /**
     * Test 7: Admin ticket detail, replying workflow, and status updating.
     */
    public function test_admin_ticket_detail_and_reply_workflow(): void
    {
        $ticket = SupportTicket::create([
            'customer_id'     => $this->customer->id,
            'subject'         => 'Courier tracking question',
            'category'        => 'order',
            'order_code'      => 'ORD-AWB-888',
            'status'          => 'awaiting_admin',
            'last_message_at' => now(),
        ]);
        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => $this->customer->id,
            'sender_role' => 'customer',
            'message'     => 'When will the courier deliver my package?',
        ]);

        // 1. Admin views ticket detail
        $resShow = $this->actingAs($this->admin)->get(route('admin.support.show', $ticket));
        $resShow->assertStatus(200);
        $resShow->assertSee('Ticket #' . $ticket->id);
        $resShow->assertSee('When will the courier deliver my package?');
        $resShow->assertSee($this->customer->name);

        // 2. Admin replies
        $resReply = $this->actingAs($this->admin)->post(route('admin.support.reply', $ticket), [
            'message' => 'Your order is currently out for delivery today.',
        ]);
        $resReply->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('awaiting_customer', $ticket->status);
        $this->assertDatabaseHas('support_messages', [
            'ticket_id'   => $ticket->id,
            'sender_id'   => $this->admin->id,
            'sender_role' => 'admin',
            'message'     => 'Your order is currently out for delivery today.',
        ]);

        // 3. Admin updates status to closed
        $resStatus = $this->actingAs($this->admin)->post(route('admin.support.status', $ticket), [
            'status' => 'closed',
        ]);
        $resStatus->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
        $this->assertEquals($this->admin->id, $ticket->closed_by);

        // 4. Admin cannot reply to closed ticket
        $resClosedReply = $this->actingAs($this->admin)->post(route('admin.support.reply', $ticket), [
            'message' => 'Trying to reply when closed.',
        ]);
        $resClosedReply->assertStatus(403);

        // 5. Admin reopens ticket
        $this->actingAs($this->admin)->post(route('admin.support.status', $ticket), [
            'status' => 'open',
        ]);
        $ticket->refresh();
        $this->assertEquals('open', $ticket->status);
        $this->assertNull($ticket->closed_at);
        $this->assertNull($ticket->closed_by);
    }

    /**
     * Test 8: Responsive styling, word-wrapping, and duplicate submission safeguards.
     */
    public function test_responsive_styles_and_duplicate_submission_safeguards(): void
    {
        $ticket = SupportTicket::create([
            'customer_id'     => $this->customer->id,
            'subject'         => 'SuperLongSubjectWithoutSpaces_1234567890_ABCDEFGHIJ_KLMNOPQRST_UVWXYZ',
            'category'        => 'general',
            'status'          => 'open',
            'last_message_at' => now(),
        ]);
        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => $this->customer->id,
            'sender_role' => 'customer',
            'message'     => 'SuperLongMessageWithoutSpaces_1234567890_ABCDEFGHIJ_KLMNOPQRST_UVWXYZ',
        ]);

        // Customer show view: word wrap + onsubmit guards + close confirm
        $resCustShow = $this->actingAs($this->customer, 'customer')->get(route('shop.support.show', ['ticket' => $ticket->id]));
        $resCustShow->assertStatus(200);
        $resCustShow->assertSee('word-break: break-word', false);
        $resCustShow->assertSee('overflow-wrap: anywhere', false);
        $resCustShow->assertSee('onsubmit=', false);
        $resCustShow->assertSee('confirm(', false);

        // Admin show view: word wrap + onsubmit guards
        $resAdminShow = $this->actingAs($this->admin)->get(route('admin.support.show', $ticket));
        $resAdminShow->assertStatus(200);
        $resAdminShow->assertSee('word-break: break-word', false);
        $resAdminShow->assertSee('overflow-wrap: anywhere', false);
        $resAdminShow->assertSee('onsubmit=', false);
    }

    /**
     * Test 9: WhatsApp Absolute Protection Audit.
     * Asserts that WhatsApp integration, settings, routes, and behavior are 100% untouched.
     */
    public function test_whatsapp_protection_audit(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.settings.whatsapp'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.orders.send-whatsapp'));

        // StoreSetting phone normalization & WhatsApp URL generation
        $normalized = StoreSetting::normalizePhoneNumber('9876543210');
        $this->assertEquals('919876543210', $normalized);

        $url = StoreSetting::getWhatsAppUrl('Hello support');
        $this->assertStringContainsString('https://wa.me/', $url);

        // Help page WhatsApp button presence
        $resHelp = $this->get(route('shop.help'));
        $resHelp->assertStatus(200);
        $resHelp->assertSee('wa-btn', false);
        $resHelp->assertSee('fab fa-whatsapp', false);
    }

    /**
     * Test 10: Regression safeguards for Phases 1–5.
     */
    public function test_phases_1_to_5_regression_safeguards(): void
    {
        // 1. Admin account dropdown
        $resDash = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $resDash->assertStatus(200);
        $resDash->assertSee('admin-user-btn', false);
        $resDash->assertSee('admin-user-dropdown', false);
        $resDash->assertSee(url('admin/account'), false);

        // 2. Reviews moderation
        $resReviews = $this->actingAs($this->admin)->get(route('admin.reviews.index'));
        $resReviews->assertStatus(200);

        // 3. Cancellations & Returns
        $resReturns = $this->actingAs($this->admin)->get(route('admin.cancellations-returns.index'));
        $resReturns->assertStatus(200);

        // 4. Reports
        $resReports = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $resReports->assertStatus(200);
    }
}
