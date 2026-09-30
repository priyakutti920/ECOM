# Nool & Crop — Modern E-Commerce Platform

A production-grade, full-featured E-Commerce web application built with **Laravel 12.x**, **PHP 8.2+**, and **MySQL**. Designed for high-concurrency retail operations with integrated multi-channel UPI payments, automated invoice generation, dynamic store customization, responsive catalog browsing, and role-based administration.

---

## Technical Architecture & Stack

- **Framework**: Laravel 12.x (with modern PHP 8.2+ features)
- **Database**: MySQL 8.0+
- **Front-end Storefront**: Semantic HTML5, CSS3 Custom Properties (Design System), Vanilla JavaScript
- **Admin Panel**: Bootstrap 3.4.1 (jQuery 3.7.1 compatible), Summernote WYSIWYG editor
- **Queue & Background Jobs**: Database / Redis queue worker (`php artisan queue:work`)
- **PDF Generation**: Barryvdh DomPDF for tax invoices
- **Storage**: Laravel Public Storage Disk with legacy asset fallback

---

## Core Features & Security Hardening

- **Zero PII Leakage**:
  - Secure order tracking via exact 10-digit Indian mobile validation and non-sequential public reference codes (`NS-XXXXXX`).
  - Sensitive contact information masked in public tracking views.
- **Hardened Authentication**:
  - Cryptographically secure 6-digit OTPs via `random_int(0, 999999)` with strict rate-limiting (max 5 failed attempts per window).
  - OTP and password reset tokens completely excluded from logs, error messages, and frontend responses.
  - Granular route-level rate throttling across customer and admin auth endpoints.
- **Financial & Payment Integrity**:
  - Multi-state UPI payment lifecycle (`PENDING`, `SUCCESS`, `FAILED`, `CANCELLED`, `EXPIRED`).
  - Strict server-side amount verification prior to order materialisation (`abs($paidAmount - $expectedAmount) <= 0.01`).
  - Idempotent payment webhook processing preventing duplicate orders, duplicate payments, or inventory double-deductions.
  - Transparent bonus discount accounting separating current cart discounts from future bonus entitlements.
- **Data Integrity & Scalability**:
  - Complete soft-delete lifecycle preserving media assets upon deletion and supporting seamless restoration.
  - Pivot relationship integrity for product categories (`product_categories`).
  - Application-level caching for store configuration (`StoreSetting::getValue`) to eliminate N-query storms.
  - Query optimization preventing N+1 queries on product reviews via eager aggregation (`withAvg` / `withCount`).
  - Streamed / chunked order and invoice exports for handling 100k+ records without memory exhaustion.
  - Asynchronous background processing for order confirmation emails and PDF generation via `ShouldQueue`.

---

## Installation & Setup Guide

### 1. Prerequisites
- **PHP**: 8.2 or higher (Extensions required: `pdo_mysql`, `curl`, `mbstring`, `openssl`, `gd`, `zip`, `xml`)
- **Composer**: 2.x
- **MySQL**: 8.0 or higher
- **Node.js & npm** (optional for asset builds)

### 2. Clone and Install Dependencies
```bash
cd d:/E-Commerce
composer install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Generate an application key:
```bash
php artisan key:generate
```

Configure your database connection in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=your_password
```

Set your application timezone (default is India Standard Time):
```env
APP_TIMEZONE=Asia/Kolkata
```

### 4. Database Setup & Migrations
Run all database migrations:
```bash
php artisan migrate
```

Optional: Seed initial store configuration and demo data:
```bash
php artisan db:seed
```

### 5. Storage Symlink
Symlink the public storage disk so uploaded product images, banners, and category media are publicly accessible:
```bash
php artisan storage:link
```

### 6. Queue Worker Configuration
Email notifications and PDF invoice generation are queued to ensure instantaneous checkout response times.
In `.env`, set:
```env
QUEUE_CONNECTION=database
```
Run the queue worker:
```bash
php artisan queue:work --tries=3 --timeout=90
```

---

## Payment Configuration

### UPI Payment Gateway
Nool & Crop includes an integrated multi-tier UPI payment system supporting dynamic QR codes, UPI Intent links, and automated payment status polling.

Configure in `.env`:
```env
UPI_MERCHANT_ID=your_merchant_id
UPI_MERCHANT_KEY=your_secret_key
UPI_MERCHANT_VPA=merchant@upi
UPI_MERCHANT_NAME="Nool & Crop"
UPI_WEBHOOK_SECRET=your_webhook_signing_secret
```

- **Client Polling**: Polls `/payment/status/{orderId}` every 3 seconds up to a safe maximum duration of 3 minutes before prompting retry.
- **Webhook Endpoint**: `POST /payment/webhook` (protected by cryptographic signature verification and idempotent order mapping).

---

## Mail Configuration

Configure your SMTP or development mail server (e.g. Mailpit / Mailtrap) in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@noolandcrop.com"
MAIL_FROM_NAME="${APP_NAME}"
```

In development, using [Mailpit](https://github.com/axllent/mailpit) or Mailtrap is recommended to capture OTPs and password reset links without delivering real emails or exposing tokens to the UI/logs.

---

## Production Deployment Checklist

1. **Clear and Rebuild Caches**:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
2. **Supervisor Daemon for Queue Workers**:
   Ensure `php artisan queue:work` is managed by `systemd` or `supervisor` to guarantee background emails are processed reliably.
3. **Scheduled Tasks (Cron)**:
   Add Laravel's scheduler to crontab:
   ```cron
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```
4. **Permissions**:
   Ensure `storage/` and `bootstrap/cache/` directories are writable by the web server user (`chmod -R 775 storage bootstrap/cache`).
5. **Secure Headers & HTTPS**:
   Enforce HTTPS across all storefront and checkout routes (`APP_ENV=production` and `APP_DEBUG=false`).

---

## Running the Automated Test Suite

Run the full PHPUnit test suite:
```bash
php artisan test
```

To run individual feature tests:
```bash
php artisan test --filter=SecurityAndIntegrityTest
```
