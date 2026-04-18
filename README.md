# STOQS5 - Advanced POS & Inventory Management System

> A comprehensive Point of Sale (POS) and Inventory Management System built with Laravel and Vue.js

STOQS5 is an enterprise-grade system designed to streamline business operations. From sales and purchases to stock management, accounting, and customer relationships, STOQS5 provides an integrated solution for retail and wholesale businesses.

**Version:** 5.4  
**Status:** Stable & Production-Ready  
**License:** MIT

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [System Requirements](#-system-requirements)
- [Installation Guide](#-installation-guide)
- [Configuration](#-configuration)
- [Project Structure](#-project-structure)
- [Key Modules](#-key-modules)
- [API Documentation](#-api-documentation)
- [Database Models](#-database-models)
- [Development](#-development)
- [Testing](#-testing)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [Support & Documentation](#-support--documentation)
- [License](#-license)

---

## 🚀 Features

### Core Functionalities

#### Point of Sale (POS)
- **Fast & Intuitive Interface:** Lightning-fast transaction processing
- **Multi-Input Support:** Barcode scanners, weight scales, card readers
- **Printer Support:** Thermal printers for receipts and labels
- **Customer Display:** Real-time customer display screen
- **Offline Mode:** Continue operations when internet is unavailable

#### Inventory Management
- **Real-time Stock Tracking:** Monitor inventory levels across all warehouses
- **Multi-Warehouse Support:** Manage stock in multiple locations simultaneously
- **Stock Transfers:** Move inventory between warehouses
- **Stock Adjustments:** Correct inventory discrepancies
- **Stock Alerts:** Get notified when stock falls below minimum levels
- **Product Variants:** Support for variable products with different attributes

#### Sales Management
- **Point of Sale (POS):** Express checkout capability
- **Sales Orders:** Create and manage customer orders
- **Quotations:** Generate quotes for customers
- **Sales Returns:** Process refunds and returns
- **Draft Sales:** Save incomplete sales for later
- **Payment Tracking:** Link multiple payments to sales

#### Purchase Management
- **Purchase Orders:** Create orders from suppliers
- **Purchase Returns:** Return goods to suppliers
- **Supplier Management:** Maintain supplier details and history
- **Receiving:** Record incoming goods with verification
- **Purchase Analytics:** Track supplier performance

#### Product Management
- **Product Types:** Support for standard, variable, and combo products
- **Product Variants:** Manage products with multiple options (size, color, etc.)
- **Categories & Subcategories:** Organize products hierarchically
- **Brands:** Manage product brands
- **Units:** Define various units of measurement
- **Barcode Integration:** Automatic barcode generation and scanning
- **Product Images:** Multiple images per product
- **Featured Products:** Highlight bestsellers and promotions

#### Accounting & Financial
- **Chart of Accounts:** Complete accounting structure
- **Journal Entries:** Manual accounting entries
- **Account Ledgers:** Detailed transaction history
- **Balance Sheet:** Real-time balance sheet reports
- **Profit & Loss:** P&L statement generation
- **Financial Analysis:** Income and expense tracking
- **Deposits:** Track deposits and categories
- **Expenses:** Manage business expenses
- **Assets:** Track fixed and current assets

#### Human Resource Management (HRM)
- **Employee Management:** Maintain employee records and profiles
- **Departments:** Organize employees by department
- **Designations:** Define job titles and roles
- **Attendance:** Track employee attendance
- **Leave Management:** Manage leave requests and approvals
- **Leave Types:** Configure different leave categories
- **Holidays:** Set company holidays
- **Payroll:** Calculate and process salaries
- **Office Shifts:** Define working shifts
- **Employees Projects:** Assign employees to projects
- **Employee Tasks:** Distribute tasks to team members

#### Customer Relationship Management
- **Client Management:** Store detailed customer information
- **Customer Groups:** Organize customers by category
- **Credit Limits:** Set credit limits for customers
- **Payment History:** Track all customer transactions
- **Customer Analytics:** Monitor purchase patterns

### Advanced Features

#### E-Commerce Store
- **Online Storefront:** Full-featured e-commerce website
- **Product Catalog:** Public product display
- **Shopping Cart:** Persistent shopping functionality
- **Checkout Process:** Multi-step secure checkout
- **Customer Accounts:** Allow customers to create accounts
- **Order History:** Customers can view their past orders
- **Collections:** Organize products into manual collections
- **Newsletter:** Email subscription functionality
- **Chatbot:** AI-powered customer support chatbot
- **Product Search:** Smart product search and filtering
- **Product Comparison:** Allow customers to compare products
- **Responsive Design:** Works on desktop, tablet, and mobile

#### Payment Processing
- **Stripe Integration:** Accept credit card payments
- **Multiple Payment Methods:** Support various payment types
- **Payment Gateway Settings:** Configure payment processors
- **Payment Tracking:** Monitor all transactions
- **Refunds:** Process refunds securely
- **Payment Status Logs:** Track payment status changes

#### Notifications & Communications
- **SMS Notifications:** Send alerts via Twilio, Nexmo, or InfoBip
- **Email Notifications:** Automated email alerts
- **SMS Gateway Integration:** Configure SMS providers
- **Email Settings:** Configure mail server
- **Notification Templates:** Customize notification messages
- **Message History:** Track all sent messages

#### Reports & Analytics
- **Sales Reports:** Comprehensive sales analytics
- **Purchase Reports:** Supplier and purchase analysis
- **Inventory Reports:** Stock level and movement reports
- **User Activity Reports:** Track user actions
- **Financial Reports:** Detailed accounting reports
- **Custom Reports:** Create custom report filters and exports

#### Advanced Inventory
- **Stock Counting:** Physical inventory counts and reconciliation
- **Damage Tracking:** Record damaged inventory
- **Competitor Analysis:** Monitor competitor products
- **Import/Export:** Bulk import products via CSV/Excel
- **Barcodes:** Generate and manage product barcodes

#### Service & Repair Module
- **Service Jobs:** Create and track service requests
- **Service Technicians:** Assign technicians to jobs
- **Service Checklists:** Define service procedures
- **Service Reports:** Generate service documentation
- **Status Tracking:** Monitor repair progress

#### Additional Features
- **Multi-Language Support:** Content in multiple languages
- **Dark Mode:** Comfortable interface for night work
- **Currency Support:** Multi-currency transactions
- **Company Settings:** Configure company details
- **User Roles & Permissions:** Fine-grained access control
- **Audit Logging:** Track all system changes
- **Two-Factor Authentication:** Enhanced security
- **Data Backup:** Automated backup functionality
- **WooCommerce Sync:** Sync with WooCommerce store
- **QuickBooks Integration:** Export data to QuickBooks
- **ZATCA e-Invoicing:** KSA e-invoice compliance
- **Security Settings:** Configure security parameters

---

## 🛠 Tech Stack

### Backend
- **Framework:** Laravel 12.x (PHP 8.2+)
- **Database:** MySQL 8.0+
- **API:** RESTful API with Laravel Passport OAuth2
- **Authentication:** Laravel Passport, Cookie-based sessions
- **Queue System:** Laravel Queue (SQS, Redis, Database)

### Frontend
- **Framework:** Vue.js 2.7
- **UI Framework:** Bootstrap Vue 2.21.2
- **CSS Framework:** Bootstrap 4.5.3
- **Charts:** ApexCharts, ECharts
- **Form Validation:** VeeValidate 3.4.15
- **Rich Text Editor:** Quill 2.0.3
- **Barcode Generation:** vue-barcode
- **PDF Generation:** jsPDF with AutoTable
- **Excel Support:** SheetJS (via Maatwebsite)
- **HTTP Client:** Axios 1.7.7
- **Icons:** Bootstrap Icons 1.13.1
- **Date/Time:** Moment.js 2.29.1
- **UI Components:** BootstrapVue, vue-select, vue-tags-input, vue-clock-picker

### Build & Development Tools
- **Task Runner:** Laravel Artisan
- **Asset Bundler:** Webpack with Laravel Mix
- **CSS Preprocessing:** SASS/SCSS
- **JavaScript Processing:** Babel 7
- **Development Server:** Laravel built-in server
- **Hot Module Replacement:** Webpack hot reload

### Additional Services
- **Payment Processing:** Stripe API
- **SMS Services:** Twilio, Nexmo, InfoBip
- **Cloud Storage:** AWS S3
- **Email:** SMTP configuration
- **Authentication:** OAuth2 (Passport)

---

## 💻 System Requirements

### Minimum Requirements
- **PHP:** 8.2 or higher
- **MySQL:** 8.0+ or MariaDB 10.4+
- **Node.js:** 14.x or higher
- **Composer:** 2.0 or higher
- **Disk Space:** 500MB minimum

### Recommended Requirements
- **PHP:** 8.3 or higher
- **MySQL:** 8.0.23+ (with UTF-8MB4 support)
- **Node.js:** 18.x LTS or higher
- **RAM:** 2GB minimum (4GB recommended)
- **CPU:** Multi-core processor recommended
- **Server:** Linux-based (CentOS, Ubuntu, Debian)

### Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari 14+, Chrome Mobile)

---

## 📦 Installation Guide

### Prerequisites
Ensure you have the following installed:
```bash
# Check PHP version
php --version  # Should be 8.2+

# Check Composer
composer --version

# Check Node.js
node --version  # Should be 14+
npm --version
```

### Step 1: Clone the Repository
```bash
git clone https://github.com/dekwinus/STOQS5.git
cd DawV5
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

This will install all Laravel packages and dependencies listed in `composer.json`.

### Step 3: Install Node.js Dependencies
```bash
npm install
```

This installs all frontend packages including Vue.js, Bootstrap Vue, and build tools.

### Step 4: Environment Configuration

#### Create `.env` File
```bash
cp .env.example .env
# or manually create the file with necessary variables
```

#### Edit `.env` File
```env
# Application
APP_NAME="STOQS5"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stoqs5
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@stoqs5.com
MAIL_FROM_NAME="STOQS5"

# SMS Services (Twilio Example)
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_FROM_NUMBER=+1234567890

# Payment Gateway (Stripe)
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...

# AWS S3 (Optional)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Redis (Optional, for caching)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Session & Cache
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

### Step 5: Generate Application Key
```bash
php artisan key:generate
```

This generates a unique encryption key for the application.

### Step 6: Create Database
```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE stoqs5 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 7: Run Migrations
```bash
# Run all migrations
php artisan migrate

# Or with seeding
php artisan migrate --seed
```

This creates all the necessary database tables and seeds initial data.

### Step 8: Build Frontend Assets
```bash
# For development
npm run dev

# For production (optimized)
npm run prod

# For watching file changes during development
npm run watch
```

### Step 9: Set File Permissions (Linux/Mac)
```bash
# Grant write permissions to storage and bootstrap/cache
chmod -R 775 storage bootstrap/cache
chmod -R 775 public/store_files
```

### Step 10: Start Development Server
```bash
# Method 1: Using Laravel's built-in server
php artisan serve

# Method 2: Using Artisan with custom host/port
php artisan serve --host=0.0.0.0 --port=8000
```

The application will be available at `http://localhost:8000`

### Optional: Setup Passport for API Authentication
```bash
# Create API client
php artisan passport:install

# This creates encryption keys and base clients for password and personal access tokens
```

---

## ⚙️ Configuration

### Application Configuration
All configuration files are located in the `config/` directory:

| File | Purpose |
|------|---------|
| `app.php` | Application name, timezone, locale settings |
| `auth.php` | Authentication guards and providers |
| `database.php` | Database connections |
| `cache.php` | Cache driver settings |
| `session.php` | Session configuration |
| `services.php` | Third-party service credentials |
| `mail.php` | Email configuration |
| `queue.php` | Job queue settings |
| `filesystems.php` | File storage disks |
| `accounting_v2.php` | Custom accounting module settings |

### Database Configuration
Located in `config/database.php`, ensure:
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'stoqs5'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]
```

### Payment Gateway Setup

#### Stripe Configuration
1. Get API keys from [Stripe Dashboard](https://dashboard.stripe.com)
2. Add to `.env`:
```env
STRIPE_PUBLIC_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxx
```

### SMS Gateway Setup

#### Twilio
1. Create account at [Twilio](https://www.twilio.com)
2. Get credentials from Console
3. Add to `.env`:
```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxx
TWILIO_FROM_NUMBER=+1234567890
```

#### Nexmo (Vonage)
Similar setup with Nexmo credentials in `.env`

### Email Configuration
Configure SMTP settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=ssl
```

---

## 📁 Project Structure

```
DawV5/
├── app/                          # Application code
│   ├── Console/                  # Artisan commands
│   │   ├── Commands/            # Custom commands
│   │   └── Kernel.php           # Console kernel
│   ├── Events/                  # Event classes
│   │   ├── CartUpdated.php
│   │   ├── ExpenseCreated.php
│   │   ├── PaymentCreated.php
│   │   ├── SaleCreated.php
│   │   └── ... (more events)
│   ├── Http/                    # HTTP logic
│   │   ├── Controllers/         # API/Web controllers
│   │   │   ├── PosController.php
│   │   │   ├── SalesController.php
│   │   │   ├── PurchasesController.php
│   │   │   ├── ProductsController.php
│   │   │   ├── WarehouseController.php
│   │   │   ├── ReportController.php
│   │   │   ├── PaymentSalesController.php
│   │   │   ├── SettingsController.php
│   │   │   ├── UserController.php
│   │   │   ├── Api/             # API-specific controllers
│   │   │   └── ... (50+ controllers)
│   │   ├── Middleware/          # HTTP middleware
│   │   └── Kernel.php           # HTTP kernel
│   ├── Models/                  # Eloquent models (database entities)
│   │   ├── Product.php
│   │   ├── Sale.php
│   │   ├── Purchase.php
│   │   ├── Warehouse.php
│   │   ├── User.php
│   │   ├── Customer.php (Client.php)
│   │   ├── Payment.php
│   │   ├── Inventory/
│   │   ├── Account.php
│   │   ├── Expense.php
│   │   ├── Employee.php
│   │   ├── OnlineOrder.php
│   │   ├── ServiceJob.php
│   │   └── ... (100+ models)
│   ├── Providers/               # Service providers
│   │   ├── AppServiceProvider.php
│   │   └── ... (other providers)
│   ├── Services/                # Business logic services
│   ├── Jobs/                    # Queued jobs
│   ├── Listeners/               # Event listeners
│   ├── Mail/                    # Mail classes
│   ├── Notifications/           # Notification classes
│   ├── Policies/                # Authorization policies
│   ├── Exceptions/              # Custom exceptions
│   ├── Exports/                 # Excel export classes
│   ├── Imports/                 # CSV/Excel import classes
│   ├── Traits/                  # Reusable traits
│   ├── Support/                 # Helper functions
│   │   └── store_settings.php   # Global settings helper
│   └── utils/                   # Utility files
│
├── bootstrap/                    # Application bootstrap
│   ├── app.php                  # Application initialization
│   └── cache/                   # Cached bootstrap files
│
├── config/                      # Configuration files
│   ├── app.php                  # Application config
│   ├── auth.php                 # Authentication config
│   ├── database.php             # Database config
│   ├── cache.php                # Cache config
│   ├── session.php              # Session config
│   ├── mail.php                 # Mail config
│   ├── queue.php                # Queue config
│   ├── services.php             # Services config
│   ├── filesystems.php          # Filesystem config
│   ├── accounting_v2.php        # Accounting module config
│   └── ... (more configs)
│
├── database/                    # Database files
│   ├── migrations/              # Database schema migrations
│   ├── factories/               # Model factories for testing
│   └── seeders/                 # Database seeders
│
├── public/                      # Web root (publicly accessible)
│   ├── index.php               # Application entry point
│   ├── css/                    # Compiled CSS files
│   ├── js/                     # Compiled JavaScript files
│   ├── images/                 # Static images
│   ├── fonts/                  # Web fonts
│   ├── store_files/            # User-uploaded files
│   ├── audio/                  # Audio files
│   ├── mix-manifest.json       # Asset manifest file
│   └── ...
│
├── resources/                   # Application resources
│   ├── src/                    # Vue.js and JavaScript source
│   │   ├── main.js             # Main application entry
│   │   ├── login.js            # Login page entry
│   │   ├── customer-display.js # POS customer display
│   │   ├── components/         # Vue components
│   │   ├── pages/              # Page components
│   │   ├── services/           # API service classes
│   │   └── store/              # Vuex store (state management)
│   ├── views/                  # Blade template views
│   │   ├── layouts/            # Layout templates
│   │   ├── store/              # Online store templates
│   │   └── ... (more views)
│   └── lang/                   # Language translation files
│
├── routes/                      # Application routes
│   ├── web.php                 # Web routes (sessions, views)
│   ├── api.php                 # API routes (JSON responses)
│   ├── console.php             # Console commands registration
│   └── channels.php            # Broadcast channels
│
├── storage/                     # Application storage
│   ├── app/                    # General application files
│   ├── framework/              # Framework files
│   └── logs/                   # Application logs
│
├── tests/                       # Test files
│   ├── Unit/                   # Unit tests
│   ├── Feature/                # Feature tests
│   └── TestCase.php            # Base test class
│
├── vendor/                      # Composer dependencies
│
├── .env.example                 # Environment file template
├── artisan                      # Artisan CLI
├── composer.json               # PHP dependencies
├── package.json                # Node.js dependencies
├── webpack.mix.js              # Mix/Webpack configuration
├── phpunit.xml                 # PHPUnit configuration
├── server.php                  # Development server script
├── version.txt                 # Application version
├── CHANGELOG.md                # Version history
└── README.md                   # This file
```

---

## 🚀 Key Modules

### 1. Point of Sale (POS) Module
**Controllers:** `PosController.php`
**Models:** `CashRegister.php`, `PosSetting.php`

Features:
- Quick sales entry with itemized cart
- Barcode scanning support
- Payment method selection
- Receipt generation
- Session management
- Cash register operations

### 2. Sales Module
**Path:** `app/Http/Controllers/SalesController.php`
**Models:** `Sale.php`, `SaleDetail.php`, `SaleReturn.php`

Features:
- Create and manage sales orders
- Customer association
- Multiple payment tracking
- Sales returns and refunds
- Sales approval workflow
- Sales analytics and reports

### 3. Purchase Module
**Path:** `app/Http/Controllers/PurchasesController.php`
**Models:** `Purchase.php`, `PurchaseDetail.php`, `PurchaseReturn.php`

Features:
- Manage purchase orders
- Supplier management
- Stock receiving
- Purchase returns
- Purchase analysis
- Supplier payment tracking

### 4. Inventory & Warehouse Module
**Controllers:** `WarehouseController.php`, `ProductsController.php`, `TransferController.php`, `AdjustmentController.php`
**Models:** `Warehouse.php`, `Product.php`, `Transfer.php`, `Adjustment.php`

Features:
- Multi-warehouse inventory tracking
- Inter-warehouse transfers
- Stock adjustments and counts
- Inventory reports
- Stock-keeping units (SKUs)
- Barcode management
- Stock level alerts

### 5. Product Management Module
**Path:** `app/Http/Controllers/ProductsController.php`
**Models:** `Product.php`, `ProductVariant.php`, `Category.php`, `Brand.php`

Features:
- Product CRUD operations
- Product categories and subcategories
- Product variants (with different attributes)
- Product images and descriptions
- Barcode generation
- combo Products
- Featured/SpecialProducts

### 6. Accounting Module
**Path:** `app/Http/Controllers/AccountController.php`, `AccountingV2/`
**Models:** `Account.php`, `AccountingV2/` related models

Features:
- Chart of accounts
- Journal entries
- General ledger
- Balance sheet
- Profit & loss statements
- Account reconciliation
- Financial reports

### 7. Payment Module
**Controllers:** `PaymentSalesController.php`, `PaymentPurchasesController.php`
**Models:** `PaymentSale.php`, `PaymentPurchase.php`, `PaymentMethod.php`

Features:
- Track sales payments
- Track purchase payments
- Payment reversal
- Multiple payment methods
- Payment status tracking
- Payment reconciliation

### 8. Human Resources Module
**Path:** `app/Http/Controllers/hrm/`
**Models:** `Employee.php`, `Attendance.php`, `Leave.php`, `Payroll.php`

Features:
- Employee management
- Department organization
- Attendance tracking
- Leave management
- Payroll processing
- Employee documents
- Performance tracking

### 9. Online Store Module
**Controllers:** `StoreFrontController.php`, `StoreAuthController.php`, `Api/Store/*`
**Models:** `OnlineOrder.php`, `StoreOrderItem.php`, `Collection.php`

Features:
- Public product catalog
- Shopping cart
- Checkout process
- Order management
- Customer accounts
- Newsletter subscriptions
- Product collections
- Chatbot support

### 10. Service & Repair Module
**Controllers:** `ServiceJobController.php`, `ServiceTechnicianController.php`
**Models:** `ServiceJob.php`, `ServiceTechnician.php`, `ServiceChecklistItem.php`

Features:
- Service job creation
- Technician assignment
- Service checklists
- Service reports
- Status tracking

### 11. Reporting Module
**Path:** `app/Http/Controllers/ReportController.php`

Reports Available:
- Sales reports
- Purchase reports
- Inventory reports
- Customer analytics
- Employee performance
- Financial statements
- Vendor analytics
- Stock movement

### 12. Settings & Configuration
**Controllers:** `SettingsController.php`, related controllers
**Models:** `Setting.php`, `PosSetting.php`, `StoreSetting.php`

Configurable Options:
- Company information
- Business settings
- POS settings
- Payment gateway settings
- SMS gateway settings
- Mail settings
- Security settings
- Store settings

---

## 🔌 API Documentation

### Authentication
All API requests require authentication via OAuth2 (Passport):

```bash
# Get personal access token
POST /api/getAccessToken
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

### API Headers Required
```
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

### Store API Endpoints

#### Online Store Management
```
GET    /api/admin/store/settings           # Get store settings
POST   /api/admin/store/settings           # Update store settings

GET    /api/store/orders                   # List online orders
GET    /api/store/orders/{id}              # Get order details
PATCH  /api/store/orders/{id}              # Update order status

GET    /api/store/pages                    # List store pages
POST   /api/store/pages                    # Create new page
GET    /api/store/pages/{id}               # Get page details
PUT    /api/store/pages/{id}               # Update page
DELETE /api/store/pages/{id}               # Delete page

GET    /api/store/banners                  # List banners
POST   /api/store/banners                  # Create banner
GET    /api/store/banners/{id}             # Get banner
PUT    /api/store/banners/{id}             # Update banner
DELETE /api/store/banners/{id}             # Delete banner

GET    /api/store/subscribers              # List newsletter subscribers
DELETE /api/store/subscribers/{id}         # Remove subscriber

GET    /api/store/messages                 # List contact messages
GET    /api/store/messages/{id}            # Get message details
PATCH  /api/store/messages/{id}/toggle-read # Mark as read/unread
DELETE /api/store/messages/{id}            # Delete message

GET    /api/admin/store/collections       # List collections
POST   /api/admin/store/collections       # Create collection
GET    /api/admin/store/collections/{id}  # Get collection
PUT    /api/admin/store/collections/{id}  # Update collection
DELETE /api/admin/store/collections/{id}  # Delete collection
POST   /api/admin/store/collections/{id}/products # Sync collection products

GET    /api/admin/products                # Search products
```

#### Public Store Routes
```
GET    /online_store/                     # Store homepage
GET    /online_store/shop                 # Product listing
GET    /online_store/contact              # Contact page
POST   /online_store/contact              # Send contact message
POST   /online_store/store/orders         # Create order (checkout)
GET    /online_store/collections/{slug}   # View collection

POST   /online_store/newsletter/subscribe          # Subscribe to newsletter
POST   /online_store/contact/send                  # Send contact message

GET    /online_store/chatbot                      # Chatbot interface
POST   /online_store/chatbot/message              # Send chatbot message
GET    /online_store/chatbot/suggestions          # Get suggestions
POST   /online_store/chatbot/search               # Search products via chatbot
POST   /online_store/chatbot/compare              # Compare products via chatbot
```

#### Customer Account Routes (Authenticated)
```
GET    /online_store/account              # View account details
PUT    /online_store/account              # Update account
GET    /online_store/account/orders       # View my orders
GET    /online_store/account/orders/{id}  # View order details
GET    /api/my/orders                     # Orders API
```

### Response Format
All API responses follow standard JSON format:

Success (200):
```json
{
    "data": { /* response data */ },
    "message": "Operation successful"
}
```

Error (4xx/5xx):
```json
{
    "message": "Error description",
    "errors": { /* detailed errors */ }
}
```

---

## 🗄️ Database Models

### Core Models (50+ models total)

#### Business Models
- **Product** - Product catalog
- **ProductVariant** - Product variations
- **Category** - Product categories
- **SubCategory** - Product subcategories
- **Brand** - Product brands
- **Unit** - Measurement units
- **Warehouse** - Warehouse/branch locations

#### Sales Models
- **Sale** - Sales transactions
- **SaleDetail** - Individual sale line items
- **SaleReturn** - Sales returns/refunds
- **SaleReturnDetails** - Sale return details
- **Quotation** - Sales quotations
- **QuotationDetail** - Quotation items
- **DraftSale** - Incomplete sales

#### Purchase Models
- **Purchase** - Purchase orders
- **PurchaseDetail** - Purchase line items
- **PurchaseReturn** - Purchase returns
- **PurchaseReturnDetails** - Return details
- **Provider** - Supplier information

#### Inventory Models
- **Transfer** - Inter-warehouse transfers
- **TransferDetail** - Transfer items
- **Adjustment** - Inventory adjustments
- **AdjustmentDetail** - Adjustment details
- **CountStock** - Stock count records
- **Damage** - Damaged goods tracking
- **DamageDetail** - Damaged item details

#### Financial Models
- **Account** - Chart of accounts
- **PaymentSale** - Sales payments
- **PaymentPurchase** - Purchase payments
- **PaymentMethod** - Payment types
- **Expense** - Business expenses
- **ExpenseCategory** - Expense types
- **Deposit** - Deposit tracking
- **DepositCategory** - Deposit types

#### User & Access Models
- **User** - System users
- **Role** - User roles
- **Permission** - System permissions
- **Client** - Customer records
- **EcommerceClient** - Online store customers

#### HRM Models
- **Employee** - Employee records
- **Department** - Company departments
- **Designation** - Job positions
- **Attendance** - Attendance records
- **Leave** - Leave requests
- **LeaveType** - Leave categories
- **Payroll** - Salary records
- **OfficeShift** - Work schedules

#### Online Store Models
- **OnlineOrder** - E-commerce orders
- **OnlineOrderItem** - Order line items
- **Collection** - Product collections
- **StorePage** - Store pages
- **StoreBanner** - Marketing banners
- **Subscriber** - Newsletter subscribers
- **StoreSetting** - Store configuration

#### Service Models
- **ServiceJob** - Service requests
- **ServiceTechnician** - Technician records
- **ServiceChecklistItem** - Service procedures

#### Configuration Models
- **Setting** - Global settings
- **PosSetting** - POS configuration
- **StoreSetting** - Store settings
- **Currency** - Currency definitions
- **Language** - Language support

---

## 🛠️ Development

### Available Artisan Commands

```bash
# Database commands
php artisan migrate              # Run migrations
php artisan migrate:rollback     # Rollback migrations
php artisan migrate:reset        # Reset all migrations
php artisan migrate:refresh      # Refresh migrations
php artisan migrate --seed       # Migrate and seed
php artisan db:seed             # Run seeders only
php artisan db:seed --class=UserSeeder  # Run specific seeder

# Cache commands
php artisan cache:clear         # Clear application cache
php artisan config:cache        # Cache configuration
php artisan config:clear        # Clear configuration cache

# Storage commands
php artisan storage:link        # Link storage directory

# Queue commands
php artisan queue:work          # Start queue worker
php artisan queue:listen        # Listen for jobs

# Authentication
php artisan passport:install    # Install Passport
php artisan passport:keys       # Generate Passport keys

# Tinker (interactive shell)
php artisan tinker              # Start interactive shell

# Artisan serve
php artisan serve              # Start development server
php artisan serve --host=0.0.0.0 --port=8000  # Custom host/port

# Make commands
php artisan make:model Post     # Create model
php artisan make:migration create_users_table  # Create migration
php artisan make:controller PostController     # Create controller
php artisan make:command SendEmails  # Create command
```

### Running Development Server with Watch

Terminal 1 - Asset Compilation:
```bash
npm run watch
```

Terminal 2 - Laravel Server:
```bash
php artisan serve
```

Terminal 3 (Optional) - Queue Processing:
```bash
php artisan queue:work
```

### Vue.js Component Development
Components are located in `resources/src/components/`

Example component structure:
```vue
<template>
  <div class="component">
    <h2>{{ title }}</h2>
    <!-- template -->
  </div>
</template>

<script>
export default {
  name: 'ComponentName',
  data() {
    return {
      title: 'Component Title'
    }
  }
}
</script>

<style scoped>
/* component styles */
</style>
```

### Creating API Endpoints
1. Create Controller in `app/Http/Controllers/Api/`
2. Define routes in `routes/api.php`
3. Use Eloquent models for database operations
4. Return JSON responses

---

## 🧪 Testing

### Running Tests
```bash
# Run all tests
phpunit

# Run specific test file
phpunit tests/Unit/ExampleTest.php

# Run with coverage
phpunit --coverage-html coverage

# Run feature tests only
phpunit tests/Feature
```

### Test Configuration
Tests use in-memory SQLite database configured in `phpunit.xml`:
```xml
<server name="DB_CONNECTION" value="sqlite"/>
<server name="DB_DATABASE" value=":memory:"/>
```

### Writing Tests
Create tests in `tests/Feature/` or `tests/Unit/`:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_example()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
```

---

## 🐛 Troubleshooting

### Common Issues & Solutions

#### 1. **"Class not found" errors**
```bash
# Clear and regenerate autoloader
composer dump-autoload

# Or
composer dump-autoload -o  # Optimized
```

#### 2. **Database connection errors**
- Verify MySQL is running: `mysql -u root -p`
- Check `.env` database credentials
- Ensure database exists: `CREATE DATABASE stoqs5;`
- Run: `php artisan migrate`

#### 3. **"Key path does not exist" error**
```bash
php artisan key:generate
```

#### 4. **Assets not loading (CSS/JS)**
```bash
npm run dev  # or npm run prod
php artisan cache:clear
```

#### 5. **Permission denied errors (Linux/Mac)**
```bash
chmod -R 775 storage bootstrap/cache
chmod -R 775 public/store_files
```

#### 6. **"Trying to get property of non-object" errors**
- Check model relationships
- Verify database migrations ran successfully
- Clear application cache: `php artisan cache:clear`

#### 7. **Session/Authentication issues**
```bash
php artisan session:table
php artisan migrate
```

#### 8. **Queue not processing jobs**
```bash
# Start queue worker
php artisan queue:work

# Or run failed jobs
php artisan queue:retry all
```

#### 9. **Excel/CSV import errors**
- Verify file format is correct
- Check column headers match expected format
- Ensure file is not too large (>50MB)

#### 10. **CORS/API errors**
- Check CORS middleware in `app/Http/Kernel.php`
- Verify API routes in `routes/api.php`
- Check authorization headers in requests

### Debug Mode
Enable temporary debugging in `.env`:
```env
APP_DEBUG=true
```

Check logs in `storage/logs/laravel.log` for error details.

### Performance Optimization

#### Database Optimization
```bash
php artisan config:cache   # Cache configuration
php artisan route:cache    # Cache routes
php artisan view:cache     # Compile views
```

#### Asset Optimization
```bash
npm run prod               # Production build with minification
```

---

## 🤝 Contributing

### Getting Started
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/AmazingFeature`
3. Make your changes
4. Commit: `git commit -m 'Add some AmazingFeature'`
5. Push: `git push origin feature/AmazingFeature`
6. Open a Pull Request

### Development Guidelines

#### Code Style
- Follow PSR-12 for PHP code
- Use Vue.js style guide for JavaScript
- Use 4 spaces for indentation
- Use meaningful variable/function names

#### Commit Message Format
```
[TYPE] Brief description

Detailed explanation if needed.

- Bullet points for changes
- More details
```

Types: `feat:`, `fix:`, `docs:`, `style:`, `refactor:`, `test:`

#### Before Submitting PR
- [ ] Code follows style guidelines
- [ ] All tests pass: `phpunit`
- [ ] New tests added for new features
- [ ] Documentation updated
- [ ] No console errors/warnings

---

## 📚 Support & Documentation

### Getting Help
- Check existing issues on GitHub
- Review documentation in project
- Check configuration examples
- Review controller/model examples

### Documentation
- **Installation:** See Installation Guide above
- **Configuration:** See Configuration section
- **API:** See API Documentation section
- **Models:** See Database Models section

### External Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://v2.vuejs.org)
- [Bootstrap Vue Docs](https://bootstrap-vue.org)
- [Stripe API Docs](https://stripe.com/docs)

---

## 📜 License

This project is licensed under the **MIT License** - see the LICENSE file for details.

You are free to:
- ✅ Use commercially
- ✅ Modify
- ✅ Distribute
- ✅ Sublicense

With the condition:
- ⚠️ Include license and copyright notice

---

## 📞 Contact & Support

For issues, suggestions, or questions:
- **GitHub:** https://github.com/dekwinus/STOQS5
- **Email:** support@stoqs5.com
- **Issues:** [GitHub Issues](https://github.com/dekwinus/STOQS5/issues)

---

## 🎯 Roadmap

### Upcoming Features
- [ ] Advanced AI-powered inventory predictions
- [ ] Enhanced mobile app
- [ ] GraphQL API support
- [ ] Real-time collaboration features
- [ ] Extended payment gateway integrations
- [ ] Blockchain invoice verification

### Version History
Current version: **5.4**

For detailed version history, see [CHANGELOG.md](CHANGELOG.md)

---

**Last Updated:** April 17, 2026  
**Status:** Actively Maintained & Production Ready
