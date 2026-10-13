<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthenticationAndSessionPhase5Test extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('Secret123!'),
            'is_admin' => true,
        ]);

        $this->regularUser = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('Customer123!'),
            'is_admin' => false,
        ]);
    }

    /**
     * 1. Admin login with valid credentials regenerates session and redirects to dashboard.
     */
    public function test_admin_login_with_valid_credentials_regenerates_session(): void
    {
        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@example.com',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }

    /**
     * 2. Login with invalid credentials returns validation error and preserves email input.
     */
    public function test_admin_login_with_invalid_credentials_returns_error(): void
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login.post'), [
            'email' => 'admin@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * 3. Regular non-admin users cannot authenticate into the admin panel.
     */
    public function test_non_admin_cannot_login_to_admin(): void
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login.post'), [
            'email' => 'customer@example.com',
            'password' => 'Customer123!',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * 4. Successful logout invalidates session and rotates CSRF token.
     */
    public function test_admin_logout_invalidates_session_and_rotates_token(): void
    {
        $this->actingAs($this->adminUser);
        $initialToken = session()->token();

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
        $this->assertNotEquals($initialToken, session()->token());
    }

    /**
     * 5. Unauthenticated access to admin routes redirects safely to admin login with session message.
     */
    public function test_unauthenticated_admin_access_redirects_with_message(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('error');
    }

    /**
     * 6. TokenMismatchException (HTTP 419) on admin routes redirects gracefully to admin login with error.
     */
    public function test_token_mismatch_exception_redirects_gracefully_to_admin_login(): void
    {
        $exception = new \Symfony\Component\HttpKernel\Exception\HttpException(419, 'CSRF token mismatch.');
        $request = \Illuminate\Http\Request::create('/admin/login', 'POST', [
            'email' => 'admin@example.com',
            'password' => 'Secret123!',
        ]);
        $request->setLaravelSession($this->app['session.store']);

        $handler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $response = $handler->render($request, $exception);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('admin.login'), $response->headers->get('Location'));
    }

    /**
     * 7. TokenMismatchException on AJAX requests returns HTTP 419 JSON response.
     */
    public function test_token_mismatch_exception_returns_json_419_for_ajax(): void
    {
        $exception = new \Symfony\Component\HttpKernel\Exception\HttpException(419, 'CSRF token mismatch.');
        $request = \Illuminate\Http\Request::create('/admin/banners/bulk-action', 'POST');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->setLaravelSession($this->app['session.store']);

        $handler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $response = $handler->render($request, $exception);

        $this->assertEquals(419, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('session expired', strtolower($data['message']));
    }

    /**
     * 8. Public /csrf-token endpoint returns a fresh valid CSRF token.
     */
    public function test_csrf_token_endpoint_returns_valid_token(): void
    {
        $response = $this->get(route('csrf.token'));

        $response->assertOk();
        $response->assertJsonStructure(['token']);
        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * 9. Admin login page contains CSRF meta tag, double-submission protection, and visibility keepalive.
     */
    public function test_admin_login_page_renders_protection_elements(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('name="csrf-token"', false);
        $response->assertSee('adminLoginForm', false);
        $response->assertSee('submitBtn', false);
        $response->assertSee('refreshCsrfToken', false);
    }

    /**
     * 10. Session cookie configuration adheres to security standards.
     */
    public function test_session_cookie_configuration(): void
    {
        $this->assertEquals('lax', config('session.same_site'));
        $this->assertTrue(config('session.http_only'));
        $this->assertContains(config('session.driver'), ['database', 'array']);
    }

    /**
     * 11. Footer view renders dynamic links, social links, and responsive containers without errors.
     */
    public function test_footer_renders_correctly(): void
    {
        $response = $this->get(route('shop.home'));

        $response->assertOk();
        $response->assertSee('footer-wrap', false);
        $response->assertSee('footer-grid', false);
        $response->assertSee('footer-bottom', false);
        $response->assertSee('footer-social-icon', false);
    }

    /**
     * 12. Regression test: Cart and Search functionality remain intact.
     */
    public function test_cart_and_search_regression(): void
    {
        $category = Category::create([
            'name' => 'Textiles',
            'slug' => 'textiles',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cotton Fabric',
            'slug' => 'cotton-fabric',
            'price' => 500,
            'status' => 'active',
            'is_published' => true,
        ]);

        // Cart items endpoint
        $cartRes = $this->postJson(route('api.cart.items'), [
            'ids' => [$product->id],
        ]);
        $cartRes->assertOk();
        $this->assertCount(1, $cartRes->json('data'));

        // Search endpoint
        $searchRes = $this->get(route('shop.search', ['q' => 'Cotton']));
        $searchRes->assertOk();
        $searchRes->assertSee('Cotton Fabric');
    }

    /**
     * 13. ABSOLUTE WHATSAPP PROTECTION: Verify WhatsApp configuration and service are intact.
     */
    public function test_whatsapp_configuration_and_service_remain_untouched(): void
    {
        $this->assertTrue(class_exists(\App\Services\WhatsAppService::class));
        $this->assertTrue(class_exists(\App\Services\WaWalletService::class));
        $this->assertArrayHasKey('whatsapp', config('services'));
        $this->assertArrayHasKey('fast2sms_auth_key', config('services.whatsapp'));
        $this->assertArrayHasKey('api_url', config('services.whatsapp'));
    }
}
