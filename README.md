# Vortex Commerce - Pure Vanilla E-Commerce Website

A complete, responsive, modern E-Commerce website built strictly with **pure HTML5, CSS3, and beginner-friendly JavaScript**.

- **No frameworks or libraries**: No React, Vue, Angular, jQuery, Bootstrap, or Tailwind.
- **No build tools or NPM**: Pure static files.
- **No ES modules**: Standard `<script src="...">` tags.
- **Works offline**: Directly double-click `index.html` in any browser (`file://` safe).
- **Persistent Data**: Powered by `localStorage` and `data.js`.

---

## Quick Start

Simply double-click **`index.html`** to open it in your browser (Chrome, Edge, Firefox, Safari).
No Live Server, Node.js, or backend server required!

---

## Folder Structure

```text
/
├── index.html                  # Storefront Homepage
├── shop.html                   # Catalog with filters, sorting & pagination
├── product.html                # Product detail, variation prices & reviews
├── cart.html                   # Cart management & totals
├── checkout.html               # Multi-step checkout with address validation
├── login.html                  # Customer authentication
├── register.html               # Customer registration
├── profile.html                # Customer profile & addresses
├── wishlist.html               # Wishlist management
├── orders.html                 # Order history & status
├── track-order.html            # Order lookup by ID
├── invoice.html                # Print-ready A4 invoice
├── admin/
│   ├── login.html              # Admin login with route guard
│   ├── dashboard.html          # Sales stats & overview
│   ├── categories.html         # Category CRUD & ordering
│   ├── products.html           # Product CRUD & variation pricing
│   ├── orders.html             # Order list & status changer
│   ├── banners.html            # Banner & flash sale manager
│   └── settings.html           # Store theme, SEO & mock payment config
└── assets/
    ├── css/
    │   ├── style.css           # Core design system & storefront styles
    │   └── admin.css           # Admin panel layout & tables
    ├── js/
    │   ├── data.js             # Initial mock data & localStorage sync
    │   ├── main.js             # Shared header, footer, toast, modal & badges
    │   ├── cart.js             # Cart & checkout calculations
    │   ├── auth.js             # User & auth state
    │   ├── products.js         # Filters, gallery & variations
    │   └── admin.js            # Admin panel controller
    └── img/                    # Static image assets
```

---

## Default Credentials

### Customer
- **Email:** `user@example.com`
- **Password:** `user123`

### Administrator
- **Email:** `admin@example.com`
- **Password:** `admin123`
