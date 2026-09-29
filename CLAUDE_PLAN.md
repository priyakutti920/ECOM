# Mobile OTP Login — Implementation Plan

## Overview
Add a two-step mobile OTP login flow to the existing Laravel 12 ecommerce site. The flow is:
**Enter mobile → Send OTP → Verify OTP → Logged in**

---

## Step 1 — Database

### 1a. Migration: add mobile to users
```php
// database/migrations/xxxx_add_mobile_to_users.php
$table->string('mobile', 10)->unique()->nullable()->after('email');
```

### 1b. Migration: otp_codes table
```php
// database/migrations/xxxx_create_otp_codes_table.php
$table->id();
$table->string('mobile', 10)->index();
$table->string('code', 4);
$table->string('purpose', 20)->default('login'); // login | reset etc.
$table->timestamp('expires_at');
$table->timestamp('verified_at')->nullable();
$table->timestamps();
```

### 1c. Update User model
- Add `mobile` to `$fillable`
- Add `findByMobile()` scope

---

## Step 2 — OTP Service (plain PHP, no extra package)

### app/Services/OtpService.php
- `generate(mobile)` — creates 4-digit OTP, saves to `otp_codes`, calls SMS API
- `verify(mobile, code)` — checks code + not expired + not used, marks `verified_at`
- `resend(mobile)` — deletes old OTPs, generates new one
- `sendSms(mobile, message)` — calls Fast2SMS API via cURL (GET request with query params)

### app/Models/OtpCode.php
- Eloquent model for `otp_codes` table

---

## Step 3 — LoginController

### app/Http/Controllers/Shop/LoginController.php
All methods use the `shop` layout.

| Method | Route | Purpose |
|--------|-------|---------|
| `showMobile()` | GET `/login` | Render mobile-entry form |
| `sendOtp(Request)` | POST `/login` | Validate mobile, generate OTP, redirect to `/login/otp` |
| `showOtp()` | GET `/login/otp` | Render OTP form (reads mobile from session) |
| `verifyOtp(Request)` | POST `/login/otp` | Validate OTP, log user in (create if not exists), redirect home |
| `resendOtp(Request)` | POST `/login/resend` | Delete old OTPs, regenerate, stay on OTP page |
| `logout()` | POST `/logout` | Auth logout, redirect home |

**Login logic:**
1. If mobile exists in `users` → log in that user
2. If not → create user with just mobile → log in
3. Set session `mobile` for OTP flow

---

## Step 4 — Middleware

### app/Http/Middleware/EnsureCustomerAuthenticated.php
- Reads `auth_customer` guard
- Redirects to `/login` if not authenticated

### config/auth.php additions
```php
'guards' => [
 'customer' => [
 'driver' => 'session',
 'provider' => 'customers',
 ],
],
'providers' => [
 'customers' => [
 'driver' => 'eloquent',
 'model' => App\Models\User::class,
 ],
],
```

---

## Step 5 — Views

### resources/views/shop/login/mobile.blade.php
- Extends `layouts.shop`
- Clean centered card (Amazon-style)
- Mobile input (10 digits, +91 prefix display)
- "Send OTP" button with loading state
- Error messages inline

### resources/views/shop/login/otp.blade.php
- Extends `layouts.shop`
- Shows masked mobile number ("OTP sent to +91 ****XX1234")
- 4 individual digit inputs (auto-advance between them)
- "Verify" button
- "Resend OTP" link (60s cooldown timer)
- Back to change number link

---

## Step 6 — CSS

Add login-specific styles to the existing `<style>` block in `shop.blade.php` (or `@stack('styles')`):
- Centering wrapper
- Login card styling
- OTP input boxes (4 separate boxes, auto-focus next)
- Timer display
- Mobile prefix badge

---

## Step 7 — Header update

Update `shop-header.blade.php` to show "Hello, {name}" and logout link when customer is logged in, instead of static "Hello, sign in".

---

## Files to create
- `database/migrations/xxxx_add_mobile_to_users.php`
- `database/migrations/xxxx_create_otp_codes_table.php`
- `app/Models/OtpCode.php`
- `app/Services/OtpService.php`
- `app/Http/Controllers/Shop/LoginController.php`
- `app/Http/Middleware/EnsureCustomerAuthenticated.php`
- `resources/views/shop/login/mobile.blade.php`
- `resources/views/shop/login/otp.blade.php`

## Files to modify
- `app/Models/User.php` — add mobile field
- `config/auth.php` — add customer guard
- `resources/views/layouts/shop.blade.php` — add login styles
- `resources/views/shop/partials/shop-header.blade.php` — show customer name
- `routes/web.php` — already has routes defined, no changes needed

## Env variables to add
```
FAST2SMS_API_KEY=your_api_key_here
```
