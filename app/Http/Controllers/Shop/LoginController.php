<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\StoreSetting;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\OtpService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class LoginController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    /**
     * Show registration form.
     */
    public function showRegister()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('shop.home');
        }

        $storeName = StoreSetting::getStoreName();
        $redirectTo = request('redirect', null);

        return view('shop.login.register', compact('storeName', 'redirectTo'));
    }

    /**
     * Process registration with name, email, mobile, password.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'regex:/^[0-9]{10}$/', 'unique:users,mobile'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ], [
            'email.unique' => 'An account with this email already exists.',
            'mobile.unique' => 'An account with this mobile number already exists.',
            'mobile.regex' => 'Please enter a valid 10-digit mobile number.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::guard('customer')->login($user, true);

        $url = $this->safeRedirectUrl($request->input('redirect'));

        return redirect($url)->with('success', 'Account created successfully! Welcome to '.StoreSetting::getStoreName());
    }

    /**
     * Customer login with email or mobile + password.
     */
    public function loginWithPassword(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $loginInput = trim($credentials['login']);
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        // Check user exists
        $user = User::where($field, $loginInput)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'login' => 'Invalid credentials. Please check your details and try again.',
            ])->withInput($request->only('login', 'redirect'));
        }

        Auth::guard('customer')->login($user, $remember);
        $request->session()->regenerate();

        $url = $this->safeRedirectUrl($request->input('redirect'));

        return redirect($url)->with('success', 'Welcome back, '.$user->name.'!');
    }

    /**
     * Step 1: Show email entry form (OTP flow).
     */
    public function showEmail()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('shop.home');
        }

        $storeName = StoreSetting::getStoreName();
        $redirectTo = request('redirect', null);

        return view('shop.login.email', compact('storeName', 'redirectTo'));
    }

    /**
     * Step 1 (POST): Validate email and send OTP.
     */
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        try {
            $result = $this->otpService->generate($validated['email']);

            if (! $result['success']) {
                return back()->withErrors(['email' => $result['message']])->withInput();
            }

            $request->session()->put('otp_email', strtolower($validated['email']));
            $request->session()->put('otp_sent_at', now()->toDateTimeString());
            RateLimiter::clear('verify-otp:'.strtolower($validated['email']));

            $safeRedirect = $this->safeRedirectUrl($request->input('redirect'));
            if ($safeRedirect !== route('shop.home')) {
                $request->session()->put('otp_redirect', $safeRedirect);
            }

            return redirect()
                ->route('shop.login.otp')
                ->with('status', $result['message']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        } catch (\Exception) {
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again.'])->withInput();
        }
    }

    /**
     * Step 2: Show OTP entry form.
     */
    public function showOtp(Request $request)
    {
        $email = $request->session()->get('otp_email');

        if (! $email) {
            return redirect()->route('shop.login.email');
        }

        $storeName = StoreSetting::getStoreName();
        $maskedEmail = $this->maskEmail($email);

        return view('shop.login.otp', compact('storeName', 'maskedEmail', 'email'));
    }

    /**
     * Step 2 (POST): Verify OTP and log in.
     */
    public function verifyOtp(Request $request)
    {
        $email = $request->session()->get('otp_email');

        if (! $email) {
            return redirect()->route('shop.login.email');
        }

        $throttleKey = 'verify-otp:'.strtolower($email);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            OtpCode::purgeOld($email);

            return back()->withErrors([
                'otp' => 'Maximum verification attempts exceeded. Your OTP has been invalidated. Please request a new one.',
            ]);
        }

        $validated = $request->validate([
            'otp' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ], [
            'otp.regex' => 'Please enter a valid 6-digit OTP.',
        ]);

        $result = $this->otpService->verify($email, $validated['otp']);

        if (! $result['success']) {
            RateLimiter::hit($throttleKey, 600);
            $retriesLeft = RateLimiter::retriesLeft($throttleKey, 5);
            if ($retriesLeft <= 0) {
                OtpCode::purgeOld($email);

                return back()->withErrors([
                    'otp' => 'Maximum verification attempts exceeded. Your OTP has been invalidated. Please request a new one.',
                ]);
            }

            return back()->withErrors(['otp' => $result['message']." ({$retriesLeft} attempts remaining)"])->withInput();
        }

        RateLimiter::clear($throttleKey);

        // Find existing customer by email, or create one.
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Customer '.substr(strstr($email, '@', true) ?: $email, 0, 8),
                'email' => $email,
                'password' => Hash::make(bin2hex(random_bytes(8))),
            ]);
        }

        Auth::guard('customer')->login($user, true);

        $redirectTo = $request->session()->get('otp_redirect');
        $request->session()->forget(['otp_email', 'otp_sent_at', 'otp_redirect']);

        $redirectUrl = $redirectTo ?: route('shop.home');

        return redirect($redirectUrl)->with('success', 'Welcome! You are now logged in.');
    }

    /**
     * Resend OTP.
     */
    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('otp_email');

        if (! $email) {
            return redirect()->route('shop.login.email');
        }

        try {
            $result = $this->otpService->resend($email);
            if (! $result['success']) {
                return back()->withErrors(['otp' => $result['message']]);
            }
            RateLimiter::clear('verify-otp:'.strtolower($email));

            return back()->with('status', $result['message']);
        } catch (\Exception) {
            return back()->withErrors(['otp' => 'Failed to resend OTP. Please try again.']);
        }
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.home')->with('status', 'You have been logged out.');
    }

    /**
     * Show customer profile/account page.
     */
    public function showAccount()
    {
        $customer = Auth::guard('customer')->user();
        $storeName = StoreSetting::getStoreName();
        $addresses = $customer->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('shop.account', compact('customer', 'storeName', 'addresses'));
    }

    /**
     * Save the customer's basic profile (name, mobile, avatar).
     */
    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => [
                'nullable',
                'string',
                'regex:/^[0-9]{10}$/',
                Rule::unique('users', 'mobile')->ignore($customer->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'mobile.regex' => 'Please enter a valid 10-digit mobile number.',
            'mobile.unique' => 'That mobile number is already linked to another account.',
            'avatar.image' => 'The avatar must be a valid image file.',
            'avatar.max' => 'Avatar image size cannot exceed 4MB.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'mobile' => $validated['mobile'] ?? null,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if existing
            if ($customer->avatar && Storage::disk('public')->exists($customer->avatar)) {
                Storage::disk('public')->delete($customer->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
        } elseif ($request->boolean('remove_avatar')) {
            if ($customer->avatar && Storage::disk('public')->exists($customer->avatar)) {
                Storage::disk('public')->delete($customer->avatar);
            }
            $updateData['avatar'] = null;
        }

        $customer->update($updateData);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Change password for the customer account.
     */
    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ], [
            'password.confirmed' => 'New password confirmation does not match.',
        ]);

        if (! Hash::check($validated['current_password'], $customer->password)) {
            return back()->withErrors(['current_password' => 'The current password provided is incorrect.']);
        }

        $customer->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Add/remove product from customer wishlist.
     */
    public function toggleWishlist(Request $request)
    {
        $customerId = Auth::guard('customer')->id();

        if (! $customerId) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'message' => 'Please log in to save items to your wishlist.',
            ], 401);
        }

        $productId = (int) $request->input('product_id');
        if (! $productId) {
            return response()->json(['success' => false, 'message' => 'Invalid product.'], 400);
        }

        $existing = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
            $msg = 'Removed from wishlist.';
        } else {
            Wishlist::create([
                'customer_id' => $customerId,
                'product_id' => $productId,
            ]);
            $inWishlist = true;
            $msg = 'Added to wishlist!';
        }

        $totalCount = Wishlist::where('customer_id', $customerId)->count();

        return response()->json([
            'success' => true,
            'in_wishlist' => $inWishlist,
            'count' => $totalCount,
            'message' => $msg,
        ]);
    }

    /**
     * Get saved wishlist product IDs for the customer.
     */
    public function getWishlistItems()
    {
        $customerId = Auth::guard('customer')->id();
        if (! $customerId) {
            return response()->json(['ids' => []]);
        }

        $ids = Wishlist::where('customer_id', $customerId)->pluck('product_id')->all();

        return response()->json(['ids' => $ids]);
    }

    /**
     * Mask the email for display, e.g. j***n@example.com.
     */
    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($local === '' || $domain === '') {
            return $email;
        }

        if (strlen($local) <= 2) {
            $maskedLocal = $local[0].'*';
        } else {
            $maskedLocal = $local[0].str_repeat('*', max(1, strlen($local) - 2)).substr($local, -1);
        }

        return $maskedLocal.'@'.$domain;
    }

    /**
     * Validate a redirect URL to prevent open redirect attacks.
     * Only allows relative paths (starting with /) that don't redirect to external domains.
     */
    private function safeRedirectUrl(?string $redirect): string
    {
        if (! $redirect) {
            return route('shop.home');
        }

        $decoded = urldecode($redirect);

        // Must start with a single / (not // which browsers interpret as protocol-relative URL)
        if (str_starts_with($decoded, '/') && ! str_starts_with($decoded, '//')) {
            return $decoded;
        }

        return route('shop.home');
    }

    /**
     * Show forgot password form.
     */
    public function showForgotPassword()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('shop.home');
        }

        $storeName = StoreSetting::getStoreName();

        return view('shop.login.forgot-password', compact('storeName'));
    }

    /**
     * Send password reset link to user email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $user = User::where('email', $email)->first();

        // Always show friendly message to prevent email enumeration
        if (! $user) {
            return back()->with('status', 'If an account exists for this email, you will receive a password reset link shortly.');
        }

        $plainToken = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('shop.password.reset.form', [
            'token' => $plainToken,
            'email' => $email,
        ]);

        // Attempt sending email
        $sent = $this->sendPasswordResetEmail($email, $resetUrl);

        if (! $sent) {
            Log::warning('Password reset email delivery failed.');
        }

        return back()->with('status', 'If an account exists for this email, you will receive a password reset link shortly.');
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(Request $request, string $token)
    {
        $email = $request->query('email');
        if (! $email) {
            return redirect()->route('shop.password.forgot')->withErrors(['email' => 'Invalid password reset request.']);
        }

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record || ! Hash::check($token, $record->token) || Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return redirect()->route('shop.password.forgot')->withErrors(['email' => 'This password reset link is invalid or has expired. Please request a new one.']);
        }

        $storeName = StoreSetting::getStoreName();

        return view('shop.login.reset-password', compact('storeName', 'token', 'email'));
    }

    /**
     * Process resetting the password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (! $record || ! Hash::check($request->token, $record->token) || Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return redirect()->route('shop.password.forgot')->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return redirect()->route('shop.password.forgot')->withErrors(['email' => 'User not found.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('shop.login.email')->with('success', 'Password reset successfully! Please sign in with your new password.');
    }

    /**
     * Helper to send password reset email via configured SMTP.
     */
    private function sendPasswordResetEmail(string $email, string $resetUrl): bool
    {
        $host = StoreSetting::getValue('smtp_host');
        $port = StoreSetting::getValue('smtp_port');
        $encryption = StoreSetting::getValue('smtp_encryption');
        $username = StoreSetting::getValue('smtp_username');
        $password = StoreSetting::getValue('smtp_password');
        $fromEmail = StoreSetting::getValue('smtp_from_email');
        $fromName = StoreSetting::getValue('smtp_from_name') ?: StoreSetting::getStoreName();

        if (! $host || ! $username || ! $fromEmail) {
            return false;
        }

        config([
            'mail.mailers.store_smtp' => [
                'transport' => 'smtp',
                'host' => $host,
                'port' => (int) ($port ?: 587),
                'encryption' => $encryption ?: null,
                'username' => $username,
                'password' => $password,
                'timeout' => 10,
            ],
        ]);
        Mail::purge('store_smtp');

        try {
            $storeName = StoreSetting::getStoreName();
            Mail::mailer('store_smtp')->send([], [], function ($message) use ($email, $resetUrl, $fromEmail, $fromName, $storeName) {
                $message->to($email)
                    ->from($fromEmail, $fromName)
                    ->subject("Reset your password — {$storeName}")
                    ->html(view('emails.password-reset', compact('resetUrl', 'storeName'))->render());
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Password reset email failed: '.$e->getMessage());

            return false;
        }
    }
}
