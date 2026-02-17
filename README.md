# STOQS5 - Advanced POS & Inventory Management System

STOQS5 is a comprehensive Point of Sale (POS) and Inventory Management System built with Laravel and Vue.js. It is designed to streamline business operations, from sales and purchases to stock management and accounting.

## 🚀 Features

### Core Functionalities
-   **Point of Sale (POS):** Fast and intuitive POS interface with support for barcode scanners, thermal printers, and weight scales.
-   **Inventory Management:** Track stock levels, manage warehouses, handle stock transfers, and perform stock adjustments.
-   **Sales & Purchases:** Manage quotations, sales, returns, purchases, and purchase returns.
-   **Accounting:** Integrated accounting module with Chart of Accounts, Journal Entries, Balance Sheet, and Profit & Loss reports.
-   **Product Management:** Support for standard, variable (variants), and combo products.
-   **HRM:** Basic Human Resource Management for employee and payroll management.

### Advanced Features
-   **Multi-Warehouse:** Manage stock across multiple locations.
-   **Online Store:** Built-in e-commerce storefront synchronized with your inventory.
-   **Reporting:** Extensive reporting for sales, purchases, inventory, payments, and more.
-   **SMS & Email Notifications:** Automated notifications for sales, due invoices, and more via Twilio, Nexmo, InfoBip, and others.
-   **Payment Gateways:** Integrated with Stripe for credit card payments.
-   **ZATCA e-Invoicing:** Support for KSA e-invoicing requirements.
-   **Repair/Service Module:** Manage service jobs, technicians, and repair status.

## 🛠 Tech Stack

-   **Backend:** Laravel 10/11 (PHP 8.2+)
-   **Frontend:** Vue.js 2.7, BootstrapVue
-   **Database:** MySQL
-   **Build Tools:** Webpack (Laravel Mix)

## 📦 Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/dekwinus/STOQS5.git
    cd STOQS5
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```

4.  **Environment Setup:**
    -   Copy `.env.example` to `.env`
    -   Configure your database credentials in `.env`
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Database Migration:**
    ```bash
    php artisan migrate --seed
    ```

6.  **Build Assets:**
    ```bash
    npm run dev
    # or for production
    npm run prod
    ```

7.  **Run Server:**
    ```bash
    php artisan serve
    ```

## 📜 License

This project is licensed under the MIT License.

## 🤝 Contributing

Contributions are welcome! Please fork the repository and submit a pull request.
