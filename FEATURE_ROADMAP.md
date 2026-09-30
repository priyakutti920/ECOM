# Nool & Crop — Next-Gen Feature Upgrade & Implementation Roadmap

This document outlines the strategic roadmap, architectural specifications, and implementation steps for scaling the **Nool & Crop** e-commerce platform. The upgrades are structured across four execution phases prioritized by business ROI, customer conversion rate, and operational efficiency.

---

## Strategic Overview & Priority Matrix

| Feature / Upgrade | Target Impact | Implementation Effort | Recommended Sprint |
| :--- | :--- | :--- | :---: |
| **1. Multi-Payment Gateway (Razorpay / Cashfree)** | Payment Success Rate (+18–25%) | Medium | **Sprint 1** |
| **2. Courier API Automation (Shiprocket / Delhivery)** | Shipping Ops Speed (+40%), Automated AWBs | Medium | **Sprint 1** |
| **3. Automated Transactional WhatsApp API** | Notification Open Rate (90%+), Fewer Support Inquiries | Low–Medium | **Sprint 1** |
| **4. Abandoned Cart Recovery Engine** | Recover 10–15% of Lost Sales | Medium | **Sprint 2** |
| **5. Pin Code Serviceability & Delivery ETA** | Checkout Abandonment Reduction (-12%) | Low–Medium | **Sprint 2** |
| **6. Photo Reviews & Customer Social Proof** | PDP Conversion Rate (+15–20%) | Low | **Sprint 2** |
| **7. Customer Wallet & Instant Store Credits** | Customer Retention & Zero Payment Gateway Refund Fees | Medium | **Sprint 3** |
| **8. Role-Based Admin Access (RBAC)** | Warehouse & Support Security Guardrails | Medium | **Sprint 3** |
| **9. Smart Product Upsells & Bundles** | Average Order Value (AOV) (+15%) | Low–Medium | **Sprint 3** |
| **10. PWA & WebP Image Optimization Pipeline** | Page Speed & Mobile Retention | Low–Medium | **Sprint 4** |

---

## Phase 1 — Immediate High-ROI Implementations (Sprint 1)

### 1.1 Multi-Payment Gateway (Razorpay & Cashfree)
* **Objective:** Expand beyond direct UPI and COD to accept all credit/debit cards, netbanking, wallets, and cardless EMI.
* **Architecture:**
  * Add a polymorphic payment gateway driver pattern (`App\Services\Payment\PaymentManager`).
  * Implement `RazorpayGateway` and `CashfreeGateway` implementing `PaymentGatewayInterface`.
  * Keep existing direct UPI dynamic QR and COD as fallback options.
* **Database Schema Impact:**
  * Add `gateway_payment_id`, `gateway_signature`, and `fee_deducted` to `orders` and `invoices`.
* **Security & Webhooks:**
  * Strict webhook HMAC-SHA256 signature verification.
  * Idempotency check on gateway payment reference IDs.

### 1.2 Automated Courier & Logistics Integration (Shiprocket / Delhivery)
* **Objective:** Replace manual shipment handling with 1-click label generation and real-time live GPS tracking.
* **Key Features:**
  * **1-Click AWB Generation:** Directly from the Admin Order Detail page.
  * **Automated Manifests & Shipping Labels:** Download print-ready thermal labels (4×6 inch) containing barcode and order reference.
  * **Automated Live Tracking Sync:** Courier webhooks automatically advance the order status from `packed` → `shipped` → `out_for_delivery` → `delivered`.
* **API Endpoints to Integrate:**
  * `POST /orders/create/adhoc` (Shiprocket Order Sync)
  * `POST /courier/generate/awb`
  * `GET /courier/track/awb`
  * `POST /api/webhooks/courier` (Incoming status webhooks)

### 1.3 Automated WhatsApp Cloud API Notifications
* **Objective:** Deliver instant notifications where Indian consumers are most active (90%+ open rates vs. 20% for email).
* **Automated Notification Triggers:**
  1. **Order Confirmed:** Invoice preview + order breakdown + estimated delivery date.
  2. **Order Dispatched:** Live tracking link with courier partner name and AWB number.
  3. **Out for Delivery:** Delivery executive contact note.
  4. **Delivered:** Order feedback & 1-click review request.
* **Implementation:**
  * Integrate Meta WhatsApp Business Cloud API or Aisensy/Wati webhook bridge.
  * Queue all WhatsApp dispatches using Laravel's queue worker (`SendWhatsAppNotificationJob implements ShouldQueue`).

---

## Phase 2 — Sales & Conversion Boosters (Sprint 2)

### 2.1 Abandoned Cart Recovery Engine
* **Objective:** Recover an estimated 10–15% of checkout drop-offs.
* **How It Works:**
  * Track guest email and phone number as soon as Step 1 of checkout is filled.
  * If no completed order is recorded within 1 hour:
    * **Trigger 1 (1 hour):** Friendly reminder email + WhatsApp notification (*"Forgot something? Your items are waiting"*).
    * **Trigger 2 (24 hours):** Incentive offer with a time-limited 5% discount coupon.
  * Provide a 1-click checkout recovery link that repopulates the customer's cart and address.
* **Required Queue Jobs & Schedules:**
  * Scheduled command in `app/Console/Kernel.php` running every 15 minutes:
    `$schedule->command('cart:check-abandoned')->everyFifteenMinutes();`

### 2.2 Pin Code Serviceability & Delivery Time Estimation
* **Objective:** Remove uncertainty on the Product Detail Page (PDP) before checkout.
* **Key Features:**
  * Customer enters 6-digit pin code on PDP.
  * System queries local database cache of serviceable pin codes or Shiprocket Serviceability API.
  * Instant feedback:
    * ✅ *"Delivery available by Thursday, 5 Oct"*
    * ✅ *"Cash on Delivery available"*
    * ❌ *"Currently not serviceable to this location"*

### 2.3 Product Reviews with Customer Photo Uploads
* **Objective:** Build social proof and trust with verified buyer photo submissions.
* **Key Features:**
  * Verified buyer badge on reviews.
  * Drag-and-drop customer photo upload (max 3 images, client-side compressed).
  * Admin review moderation dashboard (Approve / Reject / Feature).
  * Filter reviews by "With Photos" and star rating.

---

## Phase 3 — Operational & Admin Scaling (Sprint 3)

### 3.1 Role-Based Access Control (RBAC)
* **Objective:** Ensure team members only access sections relevant to their responsibilities.
* **Roles:**
  * **Super Admin:** Full access to financial reports, payment gateway keys, database settings, and staff accounts.
  * **Warehouse & Fulfillment Manager:** View orders, print shipping labels, update dispatch statuses, and manage physical inventory.
  * **Customer Support Executive:** View orders, respond to support tickets, and approve/process item returns.
  * **Catalog Manager:** Product CRUD, category management, pricing, and banner setup.
* **Implementation:**
  * Implement clean role/permission gates using Laravel Policies and Middlewares (`role:admin`, `role:support`, `role:warehouse`).

### 3.2 Low Stock Alerts & Inventory Threshold Management
* **Objective:** Prevent stockouts on bestselling items.
* **Key Features:**
  * Configurable `low_stock_threshold` on individual products/variations (e.g. 5 units).
  * Nightly or real-time automated email alert to store administrators when inventory dips below safety levels.
  * Admin badge and filter: *"Out of Stock"* / *"Low Stock (< 5)"*.

### 3.3 Customer Wallet & Store Credit System
* **Objective:** Instant refunds and zero payment gateway refund deduction costs.
* **Key Features:**
  * Digital customer wallet (`wallets` and `wallet_transactions` tables).
  * When an item is returned, offer:
    * **Option A:** Instant Store Credit + 5% bonus credit (keeps customer spending on-site).
    * **Option B:** Standard bank refund (takes 5–7 business days).
  * 1-click wallet balance deduction during checkout.

---

## Phase 4 — Performance, Mobile & SEO (Sprint 4)

### 4.1 Progressive Web App (PWA)
* **Objective:** Deliver an app-like experience without requiring a native Play Store / App Store build.
* **Key Features:**
  * Web App Manifest (`manifest.json`) with app icons and branded splash screen.
  * Service worker for caching static CSS, fonts, and core branding assets.
  * "Add to Home Screen" prompt for Android and iOS mobile visitors.
  * Offline fallback screen when internet connectivity drops.

### 4.2 Automated WebP Image Processing Pipeline
* **Objective:** Reduce image bandwidth by 60–70% for faster mobile load speeds.
* **Implementation:**
  * Use `Intervention Image` v3.
  * When an admin uploads JPG/PNG product photos:
    * Automatically compress and save as `.webp`.
    * Generate three responsive sizes: `thumbnail` (150×150), `medium` (600×600), and `full` (1200×1200).
    * Output modern HTML `<picture>` tags with responsive `srcset`.

### 4.3 Instant Sub-Millisecond Search (Laravel Scout)
* **Objective:** Replace SQL `LIKE` searches with instant typo-tolerant search.
* **Implementation:**
  * Integrate `laravel/scout` with **Meilisearch** or **Typesense**.
  * Instant search dropdown as user types with thumbnail, category badge, and live stock indicator.
  * Typo-tolerance (e.g., searching *"coton t-shrt"* correctly finds *"Cotton T-Shirt"*).

---

## Recommended Package Dependencies

```bash
# Payment SDKs
composer require razorpay/razorpay
composer require cashfree/cashfree-pg-laravel

# Image Processing
composer require intervention/image:^3.0

# Search
composer require laravel/scout
composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle

# Excel/CSV Reports
composer require maatwebsite/excel
```

---

## Verification & Deployment Guidelines

For every implemented upgrade:
1. Ensure all new background dispatches implement `ShouldQueue`.
2. Ensure database operations are wrapped in `DB::transaction()` where financial or inventory adjustments occur.
3. Add automated feature tests in `tests/Feature/` covering edge cases.
4. Keep the frontend responsive and compliant with existing custom CSS variables and clean UI aesthetics.
