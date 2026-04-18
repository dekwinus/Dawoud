# DawPOS - Ultimate Inventory With POS

> A premium, enterprise-grade Point of Sale and Inventory Management System optimized for the Egyptian and Middle Eastern markets.

DawPOS is a high-performance, high-aesthetic business management solution built on Laravel 12.x and Vue.js 2.7. It features a modern "Flat Modern" architecture, natively supporting Arabic (RTL) out-of-the-box with a sophisticated design system powered by the signature **Daw Green** (`#04724D`) and **Vibrant Lime** (`#3EFF8B`) palette.

---

## 🏗 High-Level Architecture

### The System DNA
DawPOS operates as a **Single Page Application (SPA)** with a **Server-Side Rendering (SSR)** handoff for initial boot performance.

*   **Backend:** Laravel 12.x serving a RESTful JSON API. Business logic is organized via the Controller-Model pattern, with heavy use of Eloquent for complex relational data.
*   **Authentication:** Dual-layer security. Laravel Sessions handle Web/Blade authentication (Login/Logout), while **Laravel Passport** (OAuth2) secures the Vue.js SPA API calls.
*   **Frontend:** Vue.js 2.7 with Vuex for state management and Vue Router for local navigation. Asset bundling is handled by Webpack via Laravel Mix.

---

## 📁 Project Directory Mapping

### Backend (`app/`)
*   `app/Http/Controllers/`: Contains 70+ controllers managing core business logic.
    *   `Api/`: API-specific endpoints for the Vue frontend.
    *   `AccountingV2/`: Modular advanced accounting logic.
    *   `hrm/`: Human Resource management controllers.
*   `app/Models/`: 100+ Eloquent models representing the refined domain (Sales, Purchases, Warehouses, etc.).
*   `app/Http/Middleware/`: Security and localization filters (e.g., `SetLocale.php`).
*   `app/Providers/`: Core service providers linking the Laravel services.

### Core Resources (`resources/`)
*   `resources/src/`: The source code for the Vue.js SPA.
    *   `views/`: Page-level components (POS, Dashboard, Inventory).
    *   `store/`: Vuex modules (`auth.js`, `language.js`, `config.js`).
    *   `containers/`: Layout wrappers (Sidebar, TopNav).
    *   `plugins/`: Custom system extensions like `DawPOSKit.js`.
*   `resources/lang/`: Localization files (Arabic focused).
*   `resources/views/`: Laravel Blade templates for server-rendered login and system layouts.

### Configuration (`config/`)
| File | Role |
| :--- | :--- |
| `app.php` | Global system settings & locale |
| `auth.php` | Authentication guards (Passport/Session) |
| `database.php` | MySQL connection & driver tuning |
| `filesystems.php` | Storage paths for images and invoices |
| `accounting_v2.php` | Advanced financial module configuration |
| `passport.php` | OAuth2 encryption and token life |

---

## 🗄 Database Reference (Schema Groups)

### 1. Inventory Management
| Table | Description |
| :--- | :--- |
| `products` | Core product registry (Standard, Variable, Combo types) |
| `warehouses` | Multi-location storage tracking |
| `product_variants` | Size/Color/IMEI variations |
| `product_warehouse` | Pivot tracking stock levels at specific locations |
| `brands` / `categories` | Hierarchical organization for reporting |

### 2. Transaction Flow (Sales & Purchases)
| Table | Description |
| :--- | :--- |
| `sales` | Sale headers (Time, Total, Customer ID) |
| `sale_details` | Itemized rows within a sale |
| `purchases` | Supply chain order tracking |
| `quotations` | Draft estimates for B2B |
| `returns` | Sales and Purchase return headers |

### 3. Financial System (Accounting V2)
| Table | Description |
| :--- | :--- |
| `acc_chart_of_accounts` | The backbone of the balance sheet |
| `acc_journal_entries` | Manual and automated financial logging |
| `accounts` | Bank/Cash account tracking |
| `payment_sales` | Link between cash inflows and specific sales |
| `expenses` | Categorized business spend tracking |

### 4. Human Resources (HRM)
| Table | Description |
| :--- | :--- |
| `employees` | Profile and salary data |
| `attendances` | Clock-in/Clock-out logging |
| `leaves` | Request and approval tracking |
| `payrolls` | Salary generation and payment status |

---

## 🛠 Exhaustive Configuration (.env)

| Key | Example Value | Role |
| :--- | :--- | :--- |
| `APP_NAME` | `DawPOS` | System name globally |
| `APP_LOCALE` | `ar` | Default system language |
| `DB_DATABASE` | `dawpos` | Target MySQL database |
| `QUEUE_CONNECTION` | `database` | Async job management (Emails/SMS) |
| `TWILIO_SID` | `ACxxx` | SMS Gateway credentials |
| `STRIPE_KEY` | `pk_test_xxx` | Payment Gateway public key |
| `MAIL_MAILER` | `smtp` | Global email logic |

---

## 📦 Scratch-Build & Installation Guide

Building DawPOS from scratch requires a calibrated environment.

### 1. Environment Prerequisites
- **PHP:** 8.2 or 8.3
- **Extensions:** `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`.
- **Node.js:** v18 LTS
- **MySQL:** 8.0+

### 2. Implementation Sequence
```bash
# 1. Clone & Core Deps
git clone https://github.com/dekwinus/Dawoud.git
composer install
npm install

# 2. Key Generation & DB Linking
cp .env.example .env
php artisan key:generate

# 3. Database Reconstruction
php artisan migrate

# 4. Data Initialization (Mandatory Seeder Order)
# Reference: DatabaseSeeder.php
php artisan db:seed

# 5. Security Token Setup
php artisan passport:install

# 6. Asset Compilation
npm run dev
```

---

## 🌍 RTL & Localization Deep-Dive

DawPOS is engineered for the Middle East.

*   **Arabic Defaults:** The root `<html>` in `master.blade.php` is hardcoded to `lang="ar"` and `dir="rtl"`.
*   **Typography:** We utilize the **Changa** Google Font, specifically chosen for its readability in high-density POS environments.
*   **Dynamic Localization:** The `SetLocale.php` middleware intercepts incoming requests to apply the correct language string from `resources/lang/ar/messages.php`.

---

## ⚡ Performance Optimization

To maintain the **Flat Modern** high-speed experience:
1.  **Skip Heavy Filters:** We avoid `backdrop-filter: blur` across the UI to ensure 60FPS on low-end shop hardware.
2.  **Asset Pipeline:** Use `npm run prod` to strip development metadata and minimize bundles.
3.  **Hardware Acceleration:** We utilize `transform: translate3d` for all animations to force GPU rendering.

---

## 📄 Final Notes

**DawPOS** is a living product. To ensure 100% project recovery, always verify that your `storage/` permissions are correctly set to `775` and that your `public/mix-manifest.json` correctly points to your minified bundles.

Developed and Refactored by **Dekwin**.
