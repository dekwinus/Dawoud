<?php

use App\Http\Controllers\Admin\StoreSettingsController as AdminStoreSettings;
use App\Http\Controllers\Api\Store\AccountPagesController;
use App\Http\Controllers\Api\Store\CheckoutController;
use App\Http\Controllers\Api\Store\MessageController;
use App\Http\Controllers\Api\Store\MyOrdersApiController;
use App\Http\Controllers\Api\Store\ChatbotController;
use App\Http\Controllers\Api\Store\NewsletterController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\StoreAuthController;
use App\Http\Controllers\StoreFrontController;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

// ------------------------------------------------------------------\\
// Passport::routes();

// Login route will be defined explicitly below with middleware

Route::get('password/find/{token}', 'PasswordResetController@find');

// Route::middleware(['web','auth:web','Is_Active'])->group(function () {
//     Route::get('/admin/store/settings', [AdminStoreSettings::class, 'show']);
//     Route::post('/admin/store/settings', [AdminStoreSettings::class, 'update']);
// });

// ------------------------------------------------------------------\\
// ONLINE STORE ROUTES

Route::middleware(['web', 'request.safety', 'store.enabled'])->group(function () {

    // Removed the 'store' prefix so that the store acts as the main application landing.
    Route::group([], function () {

        Route::get('/lang/{locale}', function ($locale) {
            $supported = ['en', 'ar'];

            // Use provided locale if supported, otherwise fallback to 'en'
            $chosen = in_array($locale, $supported, true) ? $locale : 'en';

            // Store in session
            session(['locale' => $chosen]);

            // Optionally persist for a year via cookie
            Cookie::queue('locale', $chosen, 60 * 24 * 365, '/');

            return back();
        })->name('lang.switch');

        Route::get('/', [StoreFrontController::class, 'index'])->name('store.index');
        Route::get('/shop', [StoreFrontController::class, 'shop'])->name('store.shop');
        Route::get('/contact', [StoreFrontController::class, 'contact'])->name('store.contact');
        Route::post('/contact', [StoreFrontController::class, 'sendContact'])->name('store.contact.send');
        Route::post('/store/orders', [CheckoutController::class, 'store'])->name('store.orders.store');
        Route::get('/collections/{slug}', [StoreFrontController::class, 'collection'])->name('store.collection.show');
        Route::get('/product/{id}', [StoreFrontController::class, 'product'])->name('store.product.show');
        Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
        Route::post('/contact/send', [MessageController::class, 'store'])->name('store.contact.send');
        Route::get('/wholesale-request', [StoreFrontController::class, 'wholesaleRequest'])->name('store.wholesale.request');
        Route::post('/wholesale-request', [StoreFrontController::class, 'sendWholesaleRequest'])->name('store.wholesale.send');

        // Chatbot (public, no auth required)
        Route::get('/chatbot', [ChatbotController::class, 'index'])->name('store.chatbot');
        Route::post('/chatbot/message', [ChatbotController::class, 'message'])->name('store.chatbot.message');
        Route::get('/chatbot/suggestions', [ChatbotController::class, 'suggestions'])->name('store.chatbot.suggestions');
        Route::post('/chatbot/search', [ChatbotController::class, 'searchProducts'])->name('store.chatbot.search');
        Route::post('/chatbot/compare', [ChatbotController::class, 'compareProducts'])->name('store.chatbot.compare');

        // Account pages (require login on 'store' guard)
        Route::middleware(['web', 'auth:store'])->group(function () {
            Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
            Route::get('/thank-you', [CheckoutController::class, 'thankyou'])->name('store.thankyou');

            Route::get('/account', [AccountPagesController::class, 'account'])->name('account');

            Route::put('/account', [AccountPagesController::class, 'update'])->name('account.update');

            Route::get('/account/orders', [AccountPagesController::class, 'orders'])
                ->name('account.orders');



            // Customer's own orders (JSON for the account orders table)
            Route::get('/my/orders', [MyOrdersApiController::class, 'index'])
                ->name('my_orders.index');
            // (Optional) details endpoint if you add a “view” drawer/page:
            Route::get('/my/orders/{id}', [MyOrdersApiController::class, 'show'])
                ->name('my_orders.show');
        });

        // Auth pages (only for guests of 'store' guard)
        // Note: Using '/customer' prefix to avoid collision with admin '/login'
        Route::middleware('guest:store')->group(function () {
            Route::get('/customer/login', [StoreAuthController::class, 'showLogin'])->name('store.login.show');
            Route::post('/customer/login', [StoreAuthController::class, 'login'])->name('store.login');

            Route::get('/customer/register', [StoreAuthController::class, 'showRegister'])->name('store.register.show');
            Route::post('/customer/register', [StoreAuthController::class, 'register'])->name('store.register');

        });

        // Logout (must be logged in on 'store')
        Route::post('/customer/logout', [StoreAuthController::class, 'logout'])
            ->middleware('auth:store')->name('store.logout');

    });
});



// ------------------------------------------------------------------\\



Route::group(['middleware' => ['web', 'auth:web', 'Is_Active']], function () {
    // Legacy Bridge (Redirects to new /admin prefix)
    Route::get('/app/dashboard', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', fn() => redirect()->route('dashboard'));
    Route::get('/app/products', fn() => redirect()->route('products.index'));

    // Admin aliases for migrated pages (keeps old/new menu structures working)
    Route::get('/admin/settings/system', fn() => redirect()->route('settings.index'));
    Route::get('/admin/settings/users', fn() => redirect()->route('users.index'));
    Route::get('/admin/settings/permissions', fn() => redirect()->route('roles.index'));
    Route::get('/admin/profile', fn() => redirect()->route('users.index'));

    // Restored admin modules in new Inertia design
    Route::get('/admin/products/Categories', [\App\Http\Controllers\CategorieController::class, 'indexInertia'])->name('products.categories.index');
    Route::get('/admin/products/Brands', [\App\Http\Controllers\BrandsController::class, 'indexInertia'])->name('products.brands.index');
    Route::get('/admin/products/Units', [\App\Http\Controllers\UnitsController::class, 'indexInertia'])->name('products.units.index');
    Route::get('/admin/shipments', [\App\Http\Controllers\ShipmentController::class, 'indexInertia'])->name('shipments.index');
    Route::get('/admin/purchases', [\App\Http\Controllers\PurchasesController::class, 'indexInertia'])->name('purchases.index');
    Route::get('/admin/purchases/list', [\App\Http\Controllers\PurchasesController::class, 'indexInertia']);
    Route::get('/admin/purchases/store', [\App\Http\Controllers\PurchasesController::class, 'indexInertia']);
    Route::get('/admin/accounts', [\App\Http\Controllers\AccountController::class, 'indexInertia'])->name('accounts.index');
    Route::get('/admin/accounting-v2/chart-of-accounts', [\App\Http\Controllers\AccountingV2\ChartOfAccountsController::class, 'indexInertia'])->name('accounting.chart_of_accounts.index');
    Route::get('/admin/accounting-v2/journal-entries', [\App\Http\Controllers\AccountingV2\JournalEntriesController::class, 'indexInertia'])->name('accounting.journal_entries.index');

    // Dashboard (Inertia)
    Route::get('/admin/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Users & Roles (Inertia)
    Route::get('/admin/users', [\App\Http\Controllers\UserController::class, 'indexInertia'])->name('users.index');
    Route::get('/admin/roles', [\App\Http\Controllers\PermissionsController::class, 'indexInertia'])->name('roles.index');

    // Settings (Inertia)
    Route::get('/admin/settings', [\App\Http\Controllers\SettingsController::class, 'indexInertia'])->name('settings.index');
    Route::get('/admin/pos', [\App\Http\Controllers\PosController::class, 'indexInertia'])->name('pos.index');
    Route::get('/admin/sales', [\App\Http\Controllers\SalesController::class, 'indexInertia'])->name('sales.index');

    // Products (Inertia)
    Route::get('/admin/products', [\App\Http\Controllers\ProductsController::class, 'indexInertia'])->name('products.index');
    Route::get('/admin/adjustments', [\App\Http\Controllers\AdjustmentController::class, 'indexInertia'])->name('adjustments.index');
    Route::get('/admin/reports/profit-loss', [\App\Http\Controllers\ReportController::class, 'reportProfitLoss'])->name('reports.profit_loss');
    Route::get('/admin/reports/sales', [\App\Http\Controllers\ReportController::class, 'reportSales'])->name('reports.sales');
    Route::get('/admin/reports/inventory', [\App\Http\Controllers\ReportController::class, 'reportInventory'])->name('reports.inventory');
    Route::get('/admin/products/create', [\App\Http\Controllers\ProductsController::class, 'createInertia'])->name('products.create');
    Route::get('/admin/products/{id}/edit', [\App\Http\Controllers\ProductsController::class, 'editInertia'])->name('products.edit');

    // CRM (Inertia)
    Route::get('/admin/people/customers', [\App\Http\Controllers\ClientController::class, 'indexInertia'])->name('customers.index');
    Route::get('/admin/people/suppliers', [\App\Http\Controllers\ProvidersController::class, 'indexInertia'])->name('suppliers.index');

    // Reporting Data Endpoints (JSON)
    Route::get('/admin/reports/sales-data', [\App\Http\Controllers\ReportController::class, 'Report_Sales']);
    Route::get('/admin/reports/inventory-data', [\App\Http\Controllers\ReportController::class, 'reportInventoryData']);
    Route::get('/admin/reports/profit-loss-data', [\App\Http\Controllers\ReportController::class, 'reportProfitLossData']);

    // Purchases create
    Route::get('/admin/purchases/create', [\App\Http\Controllers\PurchasesController::class, 'createInertia'])->name('purchases.create');

    // Expenses
    Route::get('/admin/expenses', [\App\Http\Controllers\ExpensesController::class, 'indexInertia'])->name('expenses.index');

    // Transfers
    Route::get('/admin/transfers', [\App\Http\Controllers\TransferController::class, 'indexInertia'])->name('transfers.index');

    // Damages
    Route::get('/admin/damages', [\App\Http\Controllers\DamageController::class, 'indexInertia'])->name('damages.index');

    // Quotations
    Route::get('/admin/quotations', [\App\Http\Controllers\QuotationsController::class, 'indexInertia'])->name('quotations.index');

    // Returns
    Route::get('/admin/returns/sales', [\App\Http\Controllers\SalesReturnController::class, 'indexInertia'])->name('returns.sales');
    Route::get('/admin/returns/purchases', [\App\Http\Controllers\PurchasesReturnController::class, 'indexInertia'])->name('returns.purchases');

    // Warehouses
    Route::get('/admin/warehouses', [\App\Http\Controllers\WarehouseController::class, 'indexInertia'])->name('warehouses.index');

    // Deposits
    Route::get('/admin/deposits', [\App\Http\Controllers\DepositsController::class, 'indexInertia'])->name('deposits.index');

    // HRM
    Route::get('/admin/hrm/employees', [\App\Http\Controllers\hrm\EmployeesController::class, 'indexInertia'])->name('hrm.employees');
    Route::get('/admin/hrm/attendance', [\App\Http\Controllers\hrm\AttendancesController::class, 'indexInertia'])->name('hrm.attendance');
    Route::get('/admin/hrm/leave', [\App\Http\Controllers\hrm\LeaveController::class, 'indexInertia'])->name('hrm.leave');
    Route::get('/admin/hrm/leave-types', [\App\Http\Controllers\hrm\LeaveTypeController::class, 'indexInertia'])->name('hrm.leave_types');
    Route::get('/admin/hrm/holiday', [\App\Http\Controllers\hrm\HolidayController::class, 'indexInertia'])->name('hrm.holiday');
    Route::get('/admin/hrm/payroll', [\App\Http\Controllers\hrm\PayrollController::class, 'indexInertia'])->name('hrm.payroll');
    Route::get('/admin/hrm/departments', [\App\Http\Controllers\hrm\DepartmentsController::class, 'indexInertia'])->name('hrm.departments');
    Route::get('/admin/hrm/designations', [\App\Http\Controllers\hrm\DesignationsController::class, 'indexInertia'])->name('hrm.designations');
    Route::get('/admin/hrm/office-shift', [\App\Http\Controllers\hrm\OfficeShiftController::class, 'indexInertia'])->name('hrm.office_shift');
    Route::get('/admin/hrm/company', [\App\Http\Controllers\hrm\CompanyController::class, 'indexInertia'])->name('hrm.company');

    // Service Jobs
    Route::get('/admin/service-jobs', [\App\Http\Controllers\ServiceJobController::class, 'indexInertia'])->name('service_jobs.index');

    // Payment Methods
    Route::get('/admin/settings/payment-methods', [\App\Http\Controllers\PaymentMethodController::class, 'indexInertia'])->name('payment_methods.index');
    Route::get('/admin/settings/custom-fields', [\App\Http\Controllers\CustomFieldController::class, 'indexInertia'])->name('custom_fields.index');

    // Cash Register
    Route::get('/admin/cash-register', [\App\Http\Controllers\CashRegisterController::class, 'indexInertia'])->name('cash_register.index');

    // Online Store
    Route::get('/admin/store/orders', [\App\Http\Controllers\Api\Store\OnlineOrdersApiController::class, 'indexInertia'])->name('store.orders');
    Route::get('/admin/store/banners', [\App\Http\Controllers\Api\Store\BannersApiController::class, 'indexInertia'])->name('store.banners');

    // Projects
    Route::get('/admin/projects', [\App\Http\Controllers\ProjectController::class, 'indexInertia'])->name('projects.index');
    Route::get('/admin/tasks', [\App\Http\Controllers\TaskController::class, 'indexInertia'])->name('tasks.index');
    Route::get('/admin/assets', [\App\Http\Controllers\AssetController::class, 'indexInertia'])->name('assets.index');

    // Accounting Reports
    Route::get('/admin/reports/trial-balance', [\App\Http\Controllers\AccountingV2\ReportsController::class, 'trialBalanceInertia'])->name('reports.trial-balance');
    Route::get('/admin/reports/balance-sheet', [\App\Http\Controllers\AccountingV2\ReportsController::class, 'balanceSheetInertia'])->name('reports.balance-sheet');
    Route::get('/admin/reports/tax-summary', [\App\Http\Controllers\AccountingV2\ReportsController::class, 'taxSummaryInertia'])->name('reports.tax-summary');

    // QuickBooks OAuth + status
    Route::get('/quickbooks/connect', [QuickBooksController::class, 'connect'])->name('quickbooks.connect');
    Route::get('/quickbooks/callback', [QuickBooksController::class, 'callback'])->name('quickbooks.callback');
});

// ------------------------------------------------------------------\\


// Laravel 12 compatibility: define auth routes explicitly (laravel/ui optional)
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Password Reset Routes...
Route::get('password/reset', 'Auth\\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\\ResetPasswordController@reset')->name('password.update');

// Email Verification Routes...
Route::get('email/verify', 'Auth\\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}/{hash}', 'Auth\\VerificationController@verify')->name('verification.verify');
Route::post('email/resend', 'Auth\\VerificationController@resend')->name('verification.resend');



// -------------------- Public Customer Display (token-guarded) --------------------
// Standalone public page that mounts its own Vue app. Does not affect existing SPA.
Route::get('/customer-display', function (HttpRequest $request) {
    $token = $request->query('token');
    if (!$token || $token !== cache('customer_display_token')) {
        abort(403, 'Unauthorized display access');
    }

    return view('customer_display');
})->middleware(['web']);
