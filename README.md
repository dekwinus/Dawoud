<div align="center">
  <img src="images/logo.png" alt="DawPOS Logo" width="120" />
</div>

# DawPOS — Ultimate Inventory With POS · v5.4

> A premium, enterprise-grade Point of Sale and Inventory Management System optimized for the Egyptian and Middle Eastern markets.

DawPOS is a high-performance, high-aesthetic business management solution built on **Laravel 12.x**, **Vue 3**, and **Inertia.js**. It features a modern SSR-first architecture, native Arabic (RTL) support out-of-the-box, and a sophisticated design system powered by the signature **Daw Green** (`#04724D`) and **Vibrant Lime** (`#3EFF8B`) brand palette.

---

## 🔄 Full Refactor: What Changed in DawV5

This version represents a **ground-up architectural refactor** of the frontend while the battle-tested Laravel 12 backend was significantly extended. Below is a precise before/after map.

### Frontend: Vue 2 → Vue 3 + Inertia.js

| Layer | Before (DawV4) | After (DawV5) |
| :--- | :--- | :--- |
| **UI Framework** | Vue.js 2.7 (Options API) | **Vue 3.5** (Composition API) |
| **Routing** | Vue Router (client-side SPA) | **Inertia.js v2** (server-driven routing) |
| **State Management** | Vuex 4 | **Inertia Shared Props** + Composables (Vuex retained for legacy modules) |
| **Build Tool** | Webpack via Laravel Mix | **Vite 6** with `laravel-vite-plugin` |
| **CSS Framework** | Custom Bootstrap SCSS | **Tailwind CSS v3.4** + `@tailwindcss/forms` |
| **Animations** | Manual CSS transitions | **`@vueuse/motion` 3.0** (declarative motion) |
| **Icons** | Iconsmind custom set | **Heroicons v2** + **Lucide Vue Next** |
| **Charts** | Chart.js 2.x | **ApexCharts 5.x** + `vue3-apexcharts` |
| **UI Primitives** | Bootstrap modals | **Headless UI v1.7** (a11y-first) |
| **Route Sharing** | Manual axios base URL | **Ziggy.js v2.6** (Laravel routes → frontend) |
| **App Entry** | `resources/src/login.js` (SPA boot) | `resources/src/app.js` (Inertia boot) |

### Backend: Laravel Extensions

| Area | V4 | V5 |
| :--- | :--- | :--- |
| **Framework** | Laravel 10 | **Laravel 12.x** |
| **PHP** | 8.1 | **PHP 8.2+** |
| **API Bridge** | Laravel Passport only | **Inertia.js** (Web) + **Passport 12** (API) |
| **Auth Flows** | Session + Passport | Session + Passport + **Inertia Auth Pages** |
| **PDF Engine** | DomPDF 2.x | **DomPDF 3.0** |
| **Excel** | Maatwebsite 3.x | **Maatwebsite/Excel 3.1** |
| **Accounting** | Basic ledger | **Accounting V2** (double-entry, chart of accounts) |
| **Integrations** | Stripe, Twilio | Stripe, Twilio, **WooCommerce**, **QuickBooks**, **ZATCA** |
| **Migrations** | ~200 | **295 migrations** |
| **Models** | ~80 | **123 Eloquent models** |
| **Controllers** | ~60 | **107 controllers** |

---

## 🏗 High-Level Architecture

### The System DNA

DawPOS V5 operates as an **Inertia.js Monolith** — a hybrid model where Laravel serves as the API and SSR coordinator, and Vue 3 handles rendering. There is no separate API boundary for the admin SPA; Inertia acts as the protocol bridge.

```
Browser
  │
  ▼
Laravel 12 Router
  │  Serves HTML shell on first request (SSR handoff)
  │  Returns JSON { component, props } on subsequent Inertia visits
  ▼
Inertia.js (v2 Protocol)
  │
  ▼
Vue 3 (Composition API)
  │  Resolves Pages from resources/src/Pages/**/*.vue
  │  Wraps in AdminLayout | PosLayout | StoreLayout
  ▼
TailwindCSS 3 + @vueuse/motion + ApexCharts
```

### Dual Authentication Model

```
Web (Blade/Inertia)  →  Laravel Session Guards
API (SPA / External) →  Laravel Passport 12 (OAuth2 + JWT)
Store (Public)       →  StoreAuth (cookie-based, separate guard)
```

---

## 📁 Project Directory Mapping

### Backend (`app/`)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/              ← REST endpoints for legacy Vue views
│   │   ├── AccountingV2/     ← Double-entry accounting (V2 module)
│   │   ├── Auth/             ← Session + Inertia auth (Login, Reset, 2FA)
│   │   ├── hrm/              ← HR: Employees, Payroll, Attendance, Leaves
│   │   ├── StoreFrontController.php   ← Public e-commerce storefront
│   │   ├── StoreAuthController.php    ← Store customer registration/login
│   │   ├── PosController.php          ← POS terminal (offline-sync aware)
│   │   ├── ReportController.php       ← 30+ report types (310 KB)
│   │   ├── SalesController.php        ← Sales CRUD + Inertia responses
│   │   ├── ProductsController.php     ← Products + variants (145 KB)
│   │   ├── SecuritySettingsController.php ← Device management, 2FA
│   │   ├── WooCommerceSyncController.php  ← WooCommerce integration
│   │   └── QuickBooksController.php       ← QuickBooks integration
│   └── Middleware/
│       ├── SetLocale.php     ← RTL/LTR + lang switching middleware
│       └── HandleInertiaRequests.php ← Inertia shared props (auth, settings)
├── Models/                   ← 123 Eloquent models
├── Support/
│   └── store_settings.php    ← Global helper (autoloaded via composer)
└── Providers/
    └── RouteServiceProvider.php
```

### Frontend (`resources/src/`)

```
resources/src/
├── app.js                    ← Inertia createInertiaApp() entry point
├── App.vue                   ← Root: loader overlay + offline-sync overlay
├── ziggy.js                  ← Auto-generated Laravel route map
│
├── Pages/                    ← Inertia page components (server-resolved)
│   ├── Auth/
│   │   ├── Login.vue
│   │   ├── ForgotPassword.vue
│   │   └── ResetPassword.vue
│   ├── Dashboard.vue
│   ├── Adjustments/Index.vue
│   ├── People/
│   │   ├── Customers.vue
│   │   └── Suppliers.vue
│   ├── Pos/
│   │   └── Terminal.vue      ← Full offline-capable POS terminal
│   ├── Products/
│   │   ├── Index.vue
│   │   └── Form.vue
│   ├── Reports/
│   │   ├── SalesReport.vue
│   │   ├── InventoryReport.vue
│   │   └── ProfitLoss.vue
│   ├── Sales/Index.vue
│   ├── Settings/
│   │   ├── Index.vue
│   │   └── Roles/Index.vue
│   ├── Store/                ← Public e-commerce storefront
│   │   ├── Index.vue         ← Homepage
│   │   ├── Shop.vue          ← Product listing
│   │   ├── Product.vue       ← Product detail + QuickView
│   │   ├── Checkout.vue      ← Checkout flow
│   │   ├── ThankYou.vue
│   │   ├── Contact.vue
│   │   ├── Chatbot.vue       ← In-store AI chatbot
│   │   ├── Auth/
│   │   │   ├── Login.vue
│   │   │   └── Register.vue
│   │   └── Account/
│   │       ├── Orders.vue
│   │       ├── OrderDetail.vue
│   │       └── Profile.vue
│   └── Users/Index.vue
│
├── Layouts/                  ← Persistent layout wrappers
│   ├── AdminLayout.vue       ← Sidebar + TopNav for admin panel
│   ├── PosLayout.vue         ← Minimal chrome for POS terminal
│   └── StoreLayout.vue       ← Public storefront header/footer/cart
│
├── Components/               ← Reusable Vue 3 components
│   ├── Admin/
│   │   ├── Sidebar.vue
│   │   └── TopNav.vue
│   ├── Pos/
│   │   ├── PosHeader.vue
│   │   ├── ProductGrid.vue
│   │   ├── CartSection.vue
│   │   └── PaymentModal.vue
│   ├── Store/
│   │   ├── CartDrawer.vue
│   │   └── QuickViewModal.vue
│   ├── Pagination.vue
│   └── ProductCard.vue
│
├── Composables/
│   └── useCart.js            ← Vue 3 composable for cart state
│
├── utils/
│   ├── index.js              ← Global utility functions (22 KB)
│   ├── priceFormat.js        ← Currency/price formatting
│   └── globalOfflineSync.js  ← IndexedDB offline POS sync engine
│
├── plugins/
│   ├── dawpos.kit.js         ← Global helpers & mixins (DawPOS Kit)
│   ├── i18n.loader.js        ← Locale loader
│   └── sweetalert2.js        ← SweetAlert2 wrapper
│
├── store/                    ← Vuex (retained for legacy admin views)
│   ├── index.js
│   └── modules/
│       ├── auth.js
│       ├── config.js
│       ├── language.js
│       ├── largeSidebar.js
│       └── compactSidebar.js
│
├── containers/layouts/       ← Legacy sidebar/topnav containers (Vue 2)
├── components/               ← Legacy lowercase components (Vue 2)
│   ├── TableComponent.vue
│   ├── ExcelExport.vue
│   ├── CustomFieldsForm.vue
│   ├── RichTextEditor.vue
│   └── breadcumb.vue
├── auth/                     ← Legacy Passport auth helpers
│   ├── authenticate.js
│   ├── IsConnected.js
│   └── index.js
├── login.js                  ← Legacy Vue 2 SPA boot (retained)
├── router.js                 ← Vue Router for legacy SPA (94 KB)
├── customer-display.js       ← Customer-facing POS second screen
└── views/                    ← Legacy Vue 2 SPA views (being phased out)
```

### Blade & PDF Layer (`resources/views/`)

```
resources/views/
├── app.blade.php             ← Inertia root shell (Admin/SPA)
├── store/                    ← Storefront blade layouts
│   └── order-show.blade.php
├── pdf/                      ← DomPDF templates (sale, purchase, etc.)
│   ├── sale_pdf.blade.php
│   ├── purchase_pdf.blade.php
│   ├── service_job_pdf.blade.php
│   └── ... (20+ PDF templates)
├── emails/                   ← Transactional email templates
│   ├── custom.blade.php
│   ├── reset_request.blade.php
│   └── two_factor_otp.blade.php
└── lang/                     ← Localization (AR, EN, FR, ...)
```

### Configuration (`config/`)

| File | Role |
| :--- | :--- |
| `app.php` | Global system settings & locale |
| `auth.php` | Authentication guards (Passport/Session/Store) |
| `database.php` | MySQL connection & driver tuning |
| `filesystems.php` | Storage paths for images and invoices |
| `accounting_v2.php` | Advanced financial module configuration |
| `passport.php` | OAuth2 encryption and token lifecycle |
| `backup.php` | Cloud backup (S3 / GDrive / Dropbox) |
| `excel.php` | Maatwebsite Excel driver config |

---

## 📦 Full Tech Stack

### Backend

| Package | Version | Purpose |
| :--- | :--- | :--- |
| `laravel/framework` | ^12.0 | Core PHP framework |
| `laravel/passport` | ^12.0 | OAuth2 API authentication |
| `inertiajs/inertia-laravel` | `*` | Frontend-backend protocol bridge |
| `tightenco/ziggy` | `*` | Share Laravel routes with JS |
| `barryvdh/laravel-dompdf` | ^3.0 | PDF generation (invoices, reports) |
| `maatwebsite/excel` | ^3.1 | Excel import/export |
| `yajra/laravel-datatables-oracle` | ^12.0 | Server-side datatables |
| `nwidart/laravel-modules` | ^8.3 | Modular architecture support |
| `stripe/stripe-php` | ^10.0 | Payment Gateway |
| `twilio/sdk` | ^7.0 | SMS (Twilio gateway) |
| `infobip/infobip-api-php-client` | 3.2.0 | SMS (Infobip gateway) |
| `aws/aws-sdk-php` | ^3.0 | Cloud storage SDK (S3) |
| `khaled.alshamaa/ar-php` | ^6.3 | Arabic language utilities |
| `intervention/image` | ^2.5 | Image resizing & processing |
| `guzzlehttp/guzzle` | ^7.0 | HTTP client |
| `lcobucci/jwt` | ^4.3 | JWT tokens (Passport support) |
| `doctrine/dbal` | ^3.0 | DB schema introspection |
| `ezyang/htmlpurifier` | ^4.16 | XSS-safe HTML sanitization |

### Frontend

| Package | Version | Purpose |
| :--- | :--- | :--- |
| `vue` | ^3.5.13 | UI framework (Composition API) |
| `@inertiajs/vue3` | ^2.0.0 | Inertia Vue 3 adapter |
| `vite` | ^6.0.3 | Build tool & dev server |
| `laravel-vite-plugin` | ^1.1.1 | Laravel + Vite integration |
| `@vitejs/plugin-vue` | ^5.2.1 | Vue SFCs in Vite |
| `tailwindcss` | ^3.4.16 | Utility-first CSS framework |
| `@tailwindcss/forms` | ^0.5.9 | Form element reset styles |
| `@vueuse/motion` | ^3.0.3 | Declarative Vue 3 animations |
| `@headlessui/vue` | ^1.7.23 | Accessible UI primitives |
| `@heroicons/vue` | ^2.2.0 | Heroicons icon library |
| `lucide-vue-next` | ^1.0.0 | Lucide icon library |
| `apexcharts` | ^5.10.6 | Chart rendering engine |
| `vue3-apexcharts` | ^1.11.1 | Vue 3 ApexCharts wrapper |
| `ziggy-js` | ^2.6.2 | Laravel named routes in JS |
| `axios` | ^1.7.9 | HTTP client (Inertia/API calls) |
| `lodash` | ^4.18.1 | Utility functions |
| `postcss` + `autoprefixer` | ^8.4 | CSS post-processing |

---

## 🗄 Database Reference (Schema Groups)

### 1. Inventory Management
| Table | Description |
| :--- | :--- |
| `products` | Core product registry (Standard, Variable, Combo, Service types) |
| `product_variants` | Size/Color/IMEI/Serial variations |
| `product_warehouse` | Pivot: stock levels per location |
| `warehouses` | Multi-location storage tracking |
| `brands` / `categories` / `sub_categories` | Hierarchical product organization |
| `units` | Unit of measure (Piece, Box, KG, etc.) |
| `adjustments` | Manual stock corrections |
| `damages` | Damaged goods tracking (deducted from stock) |
| `transfers` | Inter-warehouse stock movements (approval flow) |
| `count_stocks` | Periodic physical stock count records |

### 2. Transaction Flow (Sales & Purchases)
| Table | Description |
| :--- | :--- |
| `sales` | Sale headers (time, total, customer, seller) |
| `sale_details` | Itemized rows within a sale |
| `sale_returns` | Return headers |
| `purchases` | Supply chain order tracking |
| `purchase_returns` | Supplier return headers |
| `quotations` | Draft estimates for B2B |
| `shipments` | Delivery management |
| `payment_sales` | Cash & multi-payment inflow records |
| `payment_purchases` | Supplier payment outflow records |

### 3. Financial System (Accounting V2)
| Table | Description |
| :--- | :--- |
| `acc_chart_of_accounts` | Hierarchical COA (backbone of balance sheet) |
| `acc_journal_entries` | Manual and automated double-entry logging |
| `accounts` | Bank/Cash account tracking |
| `expenses` | Categorized business spend tracking |
| `deposits` | Cash inflow from any source |
| `transfer_moneys` | Inter-account fund transfers |
| `payrolls` | Salary generation and payment status |

### 4. Human Resources (HRM)
| Table | Description |
| :--- | :--- |
| `employees` | Profile, role, and salary data |
| `attendances` | Clock-in/Clock-out logging |
| `leaves` | Request and approval tracking |
| `payrolls` | Salary calculation and payment |

### 5. Online Storefront
| Table | Description |
| :--- | :--- |
| `store_clients` | Public store customer accounts |
| `store_orders` | E-commerce order headers |
| `store_order_items` | E-commerce order line items |
| `loyalty_points` | Customer rewards points balance |

### 6. Operations & CRM
| Table | Description |
| :--- | :--- |
| `service_jobs` | Maintenance/repair job tracking |
| `service_checklists` | Checklist templates for service jobs |
| `service_technicians` | Technician assignments |
| `bookings` | Customer appointment bookings |
| `clients` | Customer directory (with credit limits) |
| `providers` | Supplier directory |
| `custom_fields` | Dynamic custom fields for clients/suppliers |

---

## 🛠 Exhaustive Configuration (.env)

| Key | Example Value | Role |
| :--- | :--- | :--- |
| `APP_NAME` | `DawPOS` | System name globally |
| `APP_LOCALE` | `ar` | Default system language |
| `DB_DATABASE` | `dawpos` | Target MySQL database |
| `QUEUE_CONNECTION` | `database` | Async job management (Emails/SMS) |
| `TWILIO_SID` | `ACxxx` | Twilio SMS credentials |
| `TWILIO_AUTH_TOKEN` | `xxx` | Twilio auth token |
| `INFOBIP_API_KEY` | `xxx` | Infobip SMS gateway |
| `STRIPE_KEY` | `pk_test_xxx` | Stripe public key |
| `STRIPE_SECRET` | `sk_test_xxx` | Stripe secret key |
| `MAIL_MAILER` | `smtp` | Global email driver |
| `WOOCOMMERCE_URL` | `https://shop.com` | WooCommerce site URL |
| `WOOCOMMERCE_KEY` | `ck_xxx` | WooCommerce consumer key |
| `QUICKBOOKS_CLIENT_ID` | `xxx` | QuickBooks OAuth client |
| `AWS_BUCKET` | `my-bucket` | S3 cloud backup bucket |
| `FILESYSTEM_DISK` | `local` | Active storage driver |

---

## 📦 Scratch-Build & Installation Guide

### 1. Environment Prerequisites

- **PHP:** 8.2 or 8.3
- **Extensions:** `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `gd`
- **Node.js:** v18 LTS or v20 LTS
- **MySQL:** 8.0+
- **Composer:** 2.x

### 2. Implementation Sequence

```bash
# 1. Clone & Core Dependencies
git clone https://github.com/dekwinus/Dawoud.git
composer install
npm install

# 2. Environment Setup & Key Generation
cp .env.example .env
php artisan key:generate

# 3. Database Reconstruction (295 migrations)
php artisan migrate

# 4. Data Initialization (Mandatory Seeder Order)
php artisan db:seed

# 5. OAuth2 Token Setup
php artisan passport:install

# 6. Asset Compilation (Vite)
npm run dev          # Development (HMR enabled)
npm run build        # Production (chunked bundles)
```

### 3. Vite Chunk Strategy

The Vite config splits vendor bundles for optimal browser caching:

```
vendor-inertia   → @inertiajs/vue3
vendor-vue       → vue core
vendor-heroicons → @heroicons/vue
vendor           → everything else (lodash, apexcharts, etc.)
```

---

## 🗺 Refactor Roadmap & Migration Plan

### Phase 1 — Foundation ✅ (Complete)
- [x] Upgrade Laravel 10 → 12
- [x] Replace Webpack/Laravel Mix → **Vite 6**
- [x] Install and configure **Inertia.js v2**
- [x] Install **Vue 3.5** alongside Vue 2 (dual-boot)
- [x] Set up **Tailwind CSS v3.4** with Daw Green brand tokens
- [x] Configure manual chunk splitting in Vite
- [x] Add Ziggy for server-to-frontend route sharing

### Phase 2 — Auth & Core Pages ✅ (Complete)
- [x] Migrate Login → Inertia `Auth/Login.vue`
- [x] Migrate ForgotPassword → Inertia `Auth/ForgotPassword.vue`
- [x] Migrate ResetPassword → Inertia `Auth/ResetPassword.vue`
- [x] Build `AdminLayout.vue` with Inertia-aware sidebar
- [x] Build `Components/Admin/Sidebar.vue` & `TopNav.vue`
- [x] Migrate Dashboard → Inertia `Dashboard.vue` with ApexCharts

### Phase 3 — POS Terminal ✅ (Complete)
- [x] Build `PosLayout.vue` (minimal chrome, full-screen)
- [x] Build `Pages/Pos/Terminal.vue`
- [x] Build `Components/Pos/ProductGrid.vue`
- [x] Build `Components/Pos/CartSection.vue`
- [x] Build `Components/Pos/PaymentModal.vue`
- [x] Build `Components/Pos/PosHeader.vue`
- [x] Integrate `utils/globalOfflineSync.js` (IndexedDB-backed offline sales)
- [x] Wire `App.vue` offline-sync overlay with Fire event bus

### Phase 4 — Storefront ✅ (Complete)
- [x] Build `StoreLayout.vue` (header, footer, cart drawer)
- [x] Build `Components/Store/CartDrawer.vue`
- [x] Build `Components/Store/QuickViewModal.vue`
- [x] Build `Pages/Store/Index.vue` — branded homepage
- [x] Build `Pages/Store/Shop.vue` — filterable product grid
- [x] Build `Pages/Store/Product.vue` — PDP with add-to-cart
- [x] Build `Pages/Store/Checkout.vue` — full checkout flow
- [x] Build `Pages/Store/ThankYou.vue`
- [x] Build `Pages/Store/Auth/Login.vue` + `Register.vue`
- [x] Build `Pages/Store/Account/Orders.vue`, `OrderDetail.vue`, `Profile.vue`
- [x] Build `Pages/Store/Chatbot.vue` — AI chatbot interface
- [x] Build `Pages/Store/Contact.vue`
- [x] Wire `StoreFrontController.php` Inertia responses

### Phase 5 — Core Modules (In Progress)
- [x] `Pages/Products/Index.vue` with Inertia pagination
- [x] `Pages/Products/Form.vue` — Create/Edit product
- [x] `Pages/Sales/Index.vue`
- [x] `Pages/People/Customers.vue`
- [x] `Pages/People/Suppliers.vue`
- [x] `Pages/Adjustments/Index.vue`
- [x] `Pages/Users/Index.vue`
- [x] `Pages/Settings/Index.vue`, `Settings/Roles/Index.vue`
- [ ] `Pages/Reports/SalesReport.vue` — full migration to Inertia
- [ ] `Pages/Reports/InventoryReport.vue`
- [ ] `Pages/Reports/ProfitLoss.vue`
- [ ] Migrate Purchases module
- [ ] Migrate Purchase Returns module
- [ ] Migrate Sales Returns module
- [ ] Migrate HRM module (Employees, Payroll, Attendance, Leaves)
- [ ] Migrate Expenses & Deposits
- [ ] Migrate Transfers module

### Phase 6 — Advanced Features (Planned)
- [ ] Migrate Accounting V2 (COA, Journal, Trial Balance, P&L, Balance Sheet)
- [ ] Migrate Service & Maintenance module
- [ ] Migrate Booking module
- [ ] Migrate Asset Management module
- [ ] Migrate Damage management module
- [ ] WooCommerce bi-directional sync UI
- [ ] QuickBooks sync dashboard
- [ ] ZATCA (Fatoorah) e-invoice configuration UI
- [ ] Remove Vue 2 SPA shell entirely (deprecate `router.js`, legacy `views/`)

---

## 🌍 RTL & Localization Deep-Dive

DawPOS is engineered for the Middle East and Egyptian market.

- **Arabic Defaults:** The `SetLocale.php` middleware applies the correct language/direction per request. Arabic (`ar`) is the default locale.
- **Typography:** The **Changa** Google Font is used for its readability in high-density POS environments.
- **RTL Utility:** `khaled.alshamaa/ar-php` handles Arabic-specific text operations (numerals, date formatting, text shaping).
- **Database-Powered Translations:** Admins can add/edit language strings directly from the UI — no file editing required.
- **Dynamic i18n:** `plugins/i18n.loader.js` loads locale strings from the server and injects them at app boot.

---

## ⚡ Performance Architecture

### Vite Build Optimizations

1. **Manual Chunks:** Vendor code split into 4 separate bundles (`vendor-inertia`, `vendor-vue`, `vendor-heroicons`, `vendor`) for maximum caching efficiency.
2. **Hot Module Replacement:** Full HMR in development via `vite` + `laravel-vite-plugin`.
3. **Tree-Shaking:** Vite/Rollup eliminates all unused code from production bundles.

### Runtime Optimizations

1. **No Backdrop Blur:** `backdrop-filter: blur` is avoided system-wide to ensure 60FPS on low-spec POS hardware.
2. **GPU Animation:** All transitions use `transform: translate3d` to force compositor-layer rendering.
3. **Inertia Prefetch:** Subsequent page navigations load JSON diffs only — no full HTML reloads.
4. **Offline POS Sync:** The `globalOfflineSync.js` engine queues sales in IndexedDB and reconciles them against the server once connectivity is restored — without disrupting an active cart.

---

## 🔐 Security Architecture

- **Dual Auth Guards:** Laravel Session (Blade/Inertia web) and Passport OAuth2 (API consumers).
- **Inertia CSRF:** All Inertia form requests include the `X-Inertia` header + CSRF token automatically.
- **HTML Sanitization:** `ezyang/htmlpurifier` sanitizes all rich-text user inputs before persistence.
- **Login Device Management:** Track active sessions per device; force-logout specific or all devices.
- **Two-Factor Authentication:** OTP via Email (2FA controllers wired and ready).
- **Client Credit Limits:** Enforced at the API layer before any sale is committed to the database.
- **Security Settings Tab:** Dedicated system settings section for password policies and session controls.

---

## 📄 Final Notes

**DawPOS** is a living product, actively refactored by **Dekwin** for the Egyptian and Middle Eastern markets. The V5 refactor is the largest in the project's history — a full-stack modernization that preserves five years of battlefield-tested business logic while replacing the entire frontend with a modern, maintainable, and high-performance architecture.

To ensure 100% project recovery after any deployment:

- Verify `storage/` permissions are set to `775`
- Run `php artisan config:cache` + `php artisan route:cache` in production
- Run `php artisan ziggy:generate` after any route change (updates `resources/src/ziggy.js`)
- Set `QUEUE_CONNECTION=database` and run `php artisan queue:work` for email/SMS jobs

---

Developed and Refactored by **Dekwin** — DawPOS v5.4 · 2026.
