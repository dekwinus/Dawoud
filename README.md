# DawPOS - Ultimate Inventory With POS

> A premium, enterprise-grade Point of Sale and Inventory Management System optimized for the Egyptian and Middle Eastern markets.

DawPOS is a high-performance, high-aesthetic business management solution built on Laravel and Vue.js. It features a modern "Flat Modern" architecture, natively supporting Arabic (RTL) out-of-the-box with a sophisticated design system powered by the signature **Daw Green** (`#04724D`) and **Vibrant Lime** (`#3EFF8B`) palette.

**Version:** 5.5 (Refactored)  
**Region:** optimized for Egypt / Middle East  
**Typography:** Changa (Google Fonts)  
**Status:** Production-Ready  

---

## 📋 Table of Contents

- [Vision & Aesthetic](#-vision--aesthetic)
- [Key Modules](#-key-modules)
- [Tech Stack](#-tech-stack)
- [System Requirements](#-system-requirements)
- [Installation Guide](#-installation-guide)
- [Project Architecture](#-project-architecture)
- [RTL & Localization](#-rtl--localization)
- [Performance Optimization](#-performance-optimization)

---

## ✨ Vision & Aesthetic

DawPOS is designed to be more than just a utility; it is a premium branding experience. 

- **Flat Modern Design:** We removed heavy CSS blurs and transitions in favor of a crisp, high-contrast interface that eliminates UI lag.
- **Unified Brand Loader:** A sophisticated dual-ring preloader with integrated branding ensures a professional experience from the first second.
- **Arabic-First DNA:** Unlike standard systems that "patch" RTL support, DawPOS is engineered with Arabic as the primary interface language.
- **Dynamic Logos:** Full support for dynamic system rebranding via the administrative settings.

---

## 🚀 Key Modules

### 1. Point of Sale (POS)
- **Unified Interface:** Integrated barcode scanning, weight scale support, and thermal printer compatibility.
- **Offline Resilience:** Continue processing sales even during network interruptions.
- **Customer Awareness:** Built-in secondary display support for customer-facing terminals.

### 2. Inventory & Warehouse Management
- **Multi-Warehouse Tracking:** Real-time stock levels across multiple locations.
- **Inter-Warehouse Transfers:** Seamless stock movement between branches.
- **Advanced Adjustments:** Physical count reconciliation and damage tracking.

### 3. Sales & Purchase Powerhouse
- **Quotations & Pro-formas:** Professional document generation for B2B workflows.
- **Return Workflows:** Integrated refund and return logic for both customers and suppliers.
- **Payment Split:** Support for multiple payment methods per transaction.

### 4. Accounting (V2)
- **Chart of Accounts:** Professional financial structure for balance sheets and P&L.
- **Journal Entries:** Manual entry support for full financial control.
- **Automated Ledgers:** Every sale, purchase, and expense automatically maps to your accounts.

### 5. HRM & Payroll
- **Employee Lifecycle:** From attendance tracking to leave management.
- **Office Shifts:** Support for multiple working schedules.
- **Payroll Processing:** Automated salary generation.

### 6. E-Commerce Extension
- **Web Storefront:** Instantly transform your inventory into an online store.
- **Order Sync:** Direct integration between online orders and physical warehouse stock.

---

## 🛠 Tech Stack

### Backend
- **Framework:** Laravel 12.x
- **Database:** MySQL 8.0+
- **Security:** Passport OAuth2 & Session Guards
- **Architecture:** Controller-Service Pattern

### Frontend
- **Framework:** Vue.js 2.7
- **Bundler:** Webpack (Laravel Mix)
- **Styling:** SASS / Bootstrap 4 (Customized)
- **Validation:** VeeValidate 3.x
- **Animation:** CSS3 Hardware-Accelerated Transforms

---

## 💻 System Requirements

- **PHP:** 8.2 or 8.3
- **MySQL:** 8.0+
- **Node.js:** 18.x or 20.x
- **Composer:** 2.x
- **Memory:** 2GB RAM minimum (4GB recommended for build tasks)

---

## 📦 Installation Guide

### Step 1: Clone and Install
```bash
git clone https://github.com/dekwinus/Dawoud.git
cd Dawoud
composer install
npm install
```

### Step 2: Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```
*Configure your DB credentials inside the `.env` file.*

### Step 3: Database & Permissions
```bash
php artisan migrate --seed
php artisan passport:install
chmod -R 775 storage bootstrap/cache public/images
```

### Step 4: Asset Compilation
```bash
npm run dev
# or for production
npm run prod
```

---

## 🌍 RTL & Localization

DawPOS is specifically configured for the **Arabic** locale.
- **Default Language:** Arabic (`ar`)
- **Default Direction:** RTL
- **Main Font:** 'Changa', sans-serif (Optimized for Arabic readability)

To change the default language globally, visit `config/app.php` or the System Settings module in the dashboard.

---

## ⚡ Performance Optimization

To ensure a "Zero Lag" experience:
1. **Flat UI:** We avoid `backdrop-filter: blur` on low-end hardware.
2. **Optimized Assets:** Use `npm run prod` to minify all JS and CSS.
3. **Cache Management:** Use `php artisan optimize` to cache routes and config.

---

## 📄 License

Managed and Developed by **Dekwin**. Licensed under the MIT License.
