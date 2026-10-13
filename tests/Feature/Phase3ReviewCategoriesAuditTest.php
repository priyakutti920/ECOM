<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3ReviewCategoriesAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $customerUser;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'is_admin' => true,
            'name'     => 'Store Administrator',
            'email'    => 'admin@phase3-audit.com',
        ]);

        $this->customerUser = User::factory()->create([
            'is_admin' => false,
            'name'     => 'Arun Kumar',
            'email'    => 'arun@phase3-audit.com',
        ]);

        $category = Category::create([
            'name'        => 'Polo Shirts',
            'slug'        => 'polo-shirts',
            'is_active'   => true,
            'description' => 'Premium cotton polo shirts',
        ]);

        $this->product = Product::create([
            'name'        => 'Premium Oxford Cotton Polo',
            'slug'        => 'premium-oxford-cotton-polo',
            'code'        => 'POLO-OXF-01',
            'category_id' => $category->id,
            'price'       => 999.00,
            'is_active'   => true,
        ]);
    }

    /**
     * Test 1: Availability of all three review categories in the model and UI.
     */
    public function test_availability_of_all_three_review_categories(): void
    {
        // 1. Model canonical definitions
        $this->assertEquals('quality', Review::CATEGORY_QUALITY);
        $this->assertEquals('customer_service', Review::CATEGORY_CUSTOMER_SERVICE);
        $this->assertEquals('cart', Review::CATEGORY_CART);

        $expectedCategories = [
            'quality'          => 'Quality Review',
            'customer_service' => 'Customer Service Review',
            'cart'             => 'Cart Review',
        ];
        $this->assertEquals($expectedCategories, Review::CATEGORIES);

        // 2. Normalization helper
        $this->assertEquals('quality', Review::normalizeCategoryKey('quality'));
        $this->assertEquals('quality', Review::normalizeCategoryKey('Quality Review'));
        $this->assertEquals('customer_service', Review::normalizeCategoryKey('customer_service'));
        $this->assertEquals('customer_service', Review::normalizeCategoryKey('Customer Service Review'));
        $this->assertEquals('cart', Review::normalizeCategoryKey('cart'));
        $this->assertEquals('cart', Review::normalizeCategoryKey('Cart Review'));
        $this->assertEquals('quality', Review::normalizeCategoryKey('unknown_category'));

        // 3. Front-end product page review form contains all 3 categories
        $response = $this->get('/product/' . $this->product->slug);
        $response->assertStatus(200);
        $response->assertSee('name="category"', false);
        $response->assertSee('value="quality"', false);
        $response->assertSee('value="customer_service"', false);
        $response->assertSee('value="cart"', false);
        $response->assertSee('Quality Review', false);
        $response->assertSee('Customer Service Review', false);
        $response->assertSee('Cart Review', false);

        // Review title field is removed from storefront review form
        $response->assertDontSee('id="review-title-input"', false);
        $response->assertDontSee('>Review Title</label>', false);
    }

    /**
     * Test 2: Review creation and category validation.
     */
    public function test_review_creation_and_validation(): void
    {
        // Guest cannot submit
        $guestRes = $this->postJson(route('shop.reviews.store', $this->product), [
            'category' => 'quality',
            'rating'   => 5,
            'title'    => 'Great fabric',
            'comment'  => 'Super comfortable fit and finish.',
        ]);
        $guestRes->assertStatus(401);

        // Invalid rating rejected
        $invalidRatingRes = $this->actingAs($this->customerUser, 'customer')
            ->postJson(route('shop.reviews.store', $this->product), [
                'category' => 'quality',
                'rating'   => 10,
                'title'    => 'Invalid rating test',
                'comment'  => 'This rating is out of bounds.',
            ]);
        $invalidRatingRes->assertStatus(422);

        // Invalid category rejected
        $invalidCategoryRes = $this->actingAs($this->customerUser, 'customer')
            ->postJson(route('shop.reviews.store', $this->product), [
                'category' => 'unsupported_category_name',
                'rating'   => 4,
                'title'    => 'Invalid category test',
                'comment'  => 'This category should fail validation.',
            ]);
        $invalidCategoryRes->assertStatus(422);

        // Comment too short rejected
        $shortCommentRes = $this->actingAs($this->customerUser, 'customer')
            ->postJson(route('shop.reviews.store', $this->product), [
                'category' => 'quality',
                'rating'   => 5,
                'title'    => 'Short test',
                'comment'  => 'Bad',
            ]);
        $shortCommentRes->assertStatus(422);
    }

    /**
     * Test 3: Correct category saving, title formatting, and display properties for each category.
     */
    public function test_correct_category_saving_and_display(): void
    {
        // 1. Quality Review creation (submitted without title)
        $resQuality = $this->actingAs($this->customerUser, 'customer')
            ->postJson(route('shop.reviews.store', $this->product), [
                'category' => 'quality',
                'rating'   => 5,
                'comment'  => 'The fabric quality is soft and durable after washing.',
            ]);
        $resQuality->assertStatus(200);
        $resQuality->assertJson(['success' => true, 'category' => 'quality', 'category_label' => 'Quality Review']);

        $qualityReview = Review::latest('id')->first();
        $this->assertEquals('quality', $qualityReview->category);
        $this->assertEquals('Quality Review', $qualityReview->category_label);
        $this->assertEquals('[Quality Review]', $qualityReview->title);

        // 2. Customer Service Review creation (submitted without title)
        $user2 = User::factory()->create(['name' => 'Meena Sundaram', 'email' => 'meena@phase3-audit.com']);
        $resService = $this->actingAs($user2, 'customer')
            ->postJson(route('shop.reviews.store', $this->product), [
                'category' => 'customer_service',
                'rating'   => 4,
                'comment'  => 'The customer care team promptly answered my size query.',
            ]);
        $resService->assertStatus(200);
        $resService->assertJson(['success' => true, 'category' => 'customer_service', 'category_label' => 'Customer Service Review']);

        $serviceReview = Review::latest('id')->first();
        $this->assertEquals('customer_service', $serviceReview->category);
        $this->assertEquals('Customer Service Review', $serviceReview->category_label);
        $this->assertEquals('[Customer Service Review]', $serviceReview->title);

        // 3. Cart Review creation (submitted without title)
        $user3 = User::factory()->create(['name' => 'Vijay Anand', 'email' => 'vijay@phase3-audit.com']);
        $resCart = $this->actingAs($user3, 'customer')
            ->postJson(route('shop.reviews.store', $this->product), [
                'category' => 'cart',
                'rating'   => 5,
                'comment'  => 'Adding items to cart and applying the coupon was effortless.',
            ]);
        $resCart->assertStatus(200);
        $resCart->assertJson(['success' => true, 'category' => 'cart', 'category_label' => 'Cart Review']);

        $cartReview = Review::latest('id')->first();
        $this->assertEquals('cart', $cartReview->category);
        $this->assertEquals('Cart Review', $cartReview->category_label);
        $this->assertEquals('[Cart Review]', $cartReview->title);
    }

    /**
     * Test 4: Review listing, editing, and filtering in Admin Panel.
     */
    public function test_admin_review_listing_editing_and_filtering(): void
    {
        // Create 3 reviews with different categories
        $rQuality = Review::create([
            'product_id'    => $this->product->id,
            'customer_name' => 'User Q',
            'rating'        => 5,
            'title'         => Review::formatTitleWithCategory('Best Material', 'quality'),
            'comment'       => 'High thread count and great seams.',
            'is_approved'   => true,
        ]);

        $rService = Review::create([
            'product_id'    => $this->product->id,
            'customer_name' => 'User S',
            'rating'        => 4,
            'title'         => Review::formatTitleWithCategory('Friendly Staff', 'customer_service'),
            'comment'       => 'Polite support on live inquiries.',
            'is_approved'   => false,
        ]);

        $rCart = Review::create([
            'product_id'    => $this->product->id,
            'customer_name' => 'User C',
            'rating'        => 5,
            'title'         => Review::formatTitleWithCategory('Quick Ordering', 'cart'),
            'comment'       => 'Cart updated subtotal accurately.',
            'is_approved'   => true,
        ]);

        // 1. Admin Index shows all reviews and category filters
        $indexRes = $this->actingAs($this->adminUser)->get(route('admin.reviews.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Quality Review');
        $indexRes->assertSee('Customer Service');
        $indexRes->assertSee('Cart Review');
        $indexRes->assertSee('name="category"', false);

        // 2. Filter by Quality
        $filterQ = $this->actingAs($this->adminUser)->get(route('admin.reviews.index', ['category' => 'quality']));
        $filterQ->assertStatus(200);
        $filterQ->assertSee('Best Material');
        $filterQ->assertDontSee('Friendly Staff');
        $filterQ->assertDontSee('Quick Ordering');

        // 3. Filter by Customer Service
        $filterS = $this->actingAs($this->adminUser)->get(route('admin.reviews.index', ['category' => 'customer_service']));
        $filterS->assertStatus(200);
        $filterS->assertSee('Friendly Staff');
        $filterS->assertDontSee('Best Material');
        $filterS->assertDontSee('Quick Ordering');

        // 4. Filter by Cart
        $filterC = $this->actingAs($this->adminUser)->get(route('admin.reviews.index', ['category' => 'cart']));
        $filterC->assertStatus(200);
        $filterC->assertSee('Quick Ordering');
        $filterC->assertDontSee('Best Material');
        $filterC->assertDontSee('Friendly Staff');

        // 5. Admin Edit / Update Review
        $updateRes = $this->actingAs($this->adminUser)->putJson(route('admin.reviews.update', $rService), [
            'category'    => 'quality',
            'rating'      => 5,
            'title'       => 'Updated Title Now Quality',
            'comment'     => 'Revised review text after customer follow-up.',
            'is_approved' => true,
        ]);
        $updateRes->assertStatus(200);
        $updateRes->assertJson(['success' => true, 'category' => 'quality', 'category_label' => 'Quality Review']);

        $rServiceFresh = $rService->fresh();
        $this->assertEquals('quality', $rServiceFresh->category);
        $this->assertEquals('Updated Title Now Quality', $rServiceFresh->display_title);
        $this->assertTrue((bool)$rServiceFresh->is_approved);
    }

    /**
     * Test 5: Existing review records and ratings remain intact (backwards compatibility).
     */
    public function test_existing_review_records_and_ratings_remain_intact(): void
    {
        // Legacy review created before Phase 3 (no bracket prefix)
        $legacy = Review::create([
            'product_id'           => $this->product->id,
            'customer_id'          => $this->customerUser->id,
            'customer_name'        => 'Legacy Customer',
            'customer_email'       => 'legacy@example.com',
            'rating'               => 4,
            'title'                => 'Classic Traditional Shirt',
            'comment'              => 'Solid fit, no prefix in database title.',
            'is_approved'          => true,
            'is_verified_purchase' => true,
        ]);

        $this->assertEquals('quality', $legacy->category);
        $this->assertEquals('Quality Review', $legacy->category_label);
        $this->assertEquals('Classic Traditional Shirt', $legacy->display_title);
        $this->assertEquals(4, $legacy->rating);
        $this->assertTrue((bool)$legacy->is_verified_purchase);
        $this->assertTrue((bool)$legacy->is_approved);
        $this->assertEquals($this->customerUser->id, $legacy->customer->id);
        $this->assertEquals($this->product->id, $legacy->product->id);
        $this->assertTrue($this->customerUser->reviews()->where('id', $legacy->id)->exists());

        // Storefront product page displays the review with its category badge
        $pdpRes = $this->get('/product/' . $this->product->slug);
        $pdpRes->assertStatus(200);
        $pdpRes->assertSee('Solid fit, no prefix in database title.');
        $pdpRes->assertSee('Quality');
    }

    /**
     * Test 6: Appropriate verified customer purchase associations.
     */
    public function test_verified_purchase_association(): void
    {
        // 1. Customer has a paid order for this product
        $order = Order::create([
            'customer_id'         => $this->customerUser->id,
            'order_code'          => 'ORD-P3-VERIFIED',
            'contact_name'        => 'Arun Kumar',
            'contact_mobile'      => '9876543210',
            'contact_email'       => 'arun@phase3-audit.com',
            'addr_full_name'      => 'Arun Kumar',
            'addr_line_1'         => '12 North Mada Street',
            'addr_city'           => 'Chennai',
            'addr_state'          => 'Tamil Nadu',
            'addr_pincode'        => '600001',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 999.00,
            'total'               => 999.00,
            'payment_status'      => 'paid',
            'payment_method'      => 'upi',
            'status'              => 'delivered',
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price'   => 999.00,
            'quantity'     => 1,
            'line_total'   => 999.00,
        ]);

        // Submit review
        $res = $this->actingAs($this->customerUser, 'customer')->postJson(route('shop.reviews.store', $this->product), [
            'category' => 'quality',
            'rating'   => 5,
            'title'    => 'Verified Buyer Feedback',
            'comment'  => 'Authentic high quality product received.',
        ]);
        $res->assertStatus(200);

        $saved = Review::where('customer_id', $this->customerUser->id)->first();
        $this->assertTrue((bool)$saved->is_verified_purchase, 'Must be marked verified purchase for paid order');
    }

    /**
     * Test 7: Mobile responsiveness safeguards on Review forms and tables.
     */
    public function test_mobile_responsiveness_safeguards(): void
    {
        // Admin review index responsive structure
        $adminRes = $this->actingAs($this->adminUser)->get(route('admin.reviews.index'));
        $adminContent = $adminRes->getContent();

        $this->assertStringContainsString('class="table-responsive"', $adminContent);
        $this->assertStringContainsString('class="mobile-scroll-hint"', $adminContent);
        $this->assertStringContainsString('Scroll right to view ratings', $adminContent);
        $this->assertStringContainsString('@media (max-width: 768px)', $adminContent);
        $this->assertStringContainsString('@media (max-width: 480px)', $adminContent);

        // Storefront product review form responsive structure
        $shopRes = $this->get('/product/' . $this->product->slug);
        $shopContent = $shopRes->getContent();

        $this->assertStringContainsString('id="review-form-wrap"', $shopContent);
        $this->assertStringContainsString('id="review-category-select"', $shopContent);
        $this->assertStringContainsString('.pdp-reviews-grid', $shopContent);
    }

    /**
     * Test 8: Phase 1 scrolling fixes remain intact.
     */
    public function test_phase1_scrolling_fixes_remain_intact(): void
    {
        $adminRes = $this->actingAs($this->adminUser)->get(route('admin.appearance.theme.index'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee('theme-header-bar', false);
    }

    /**
     * Test 9: Phase 2 profile icon and mobile navigation fixes remain intact.
     */
    public function test_phase2_profile_and_navigation_remain_intact(): void
    {
        $homeRes = $this->get('/');
        $homeRes->assertStatus(200);
        $homeContent = $homeRes->getContent();

        // Mobile drawer has no Home link
        preg_match('/<aside class="sidebar-menu-wrap"[^>]*>(.*?)<\/aside>/s', $homeContent, $drawerMatches);
        $this->assertNotEmpty($drawerMatches);
        $this->assertStringNotContainsString('> Home</a>', $drawerMatches[1]);

        // Bottom nav has Home link
        preg_match('/<section class="bottom-navigation-wrap[^>]*>(.*?)<\/section>/s', $homeContent, $bottomNavMatches);
        $this->assertNotEmpty($bottomNavMatches);
        $this->assertStringContainsString('<span>Home</span>', $bottomNavMatches[1]);
    }

    /**
     * Test 10: Strict WhatsApp protection audit.
     */
    public function test_whatsapp_protection_audit(): void
    {
        $this->assertTrue(file_exists(base_path('app/Services/WhatsAppService.php')));
        $this->assertTrue(file_exists(base_path('tests/Feature/WhatsAppIntegrationTest.php')));
        $this->assertTrue(file_exists(base_path('tests/Feature/WhatsAppNotificationWalletTest.php')));

        $whatsAppCode = file_get_contents(base_path('app/Services/WhatsAppService.php'));
        $this->assertStringContainsString('class WhatsAppService', $whatsAppCode);
    }
}
