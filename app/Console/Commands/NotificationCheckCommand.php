<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Models\product_warehouse;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\PaymentDue; // We might need to create this
use Illuminate\Support\Facades\Notification;

class NotificationCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for due invoices and expiring products and send notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->checkDueInvoices();
        $this->checkExpiringProducts();
        return 0;
    }

    protected function checkDueInvoices()
    {
        // Check for invoices due in exactly 14, 7, 3, or 1 days
        // Assumption: "Due" means unpaid and created X days ago.
        // Adjust logic if "Due Date" is a separate field, but usually it's based on payment terms or just "older than X".
        // Based on UserController logic: due after 14 days.
        
        $daysOverdue = [14, 21, 28]; // Notify on day 14 (due), day 21 (1 week late), day 28 (2 weeks late)

        foreach ($daysOverdue as $days) {
            $date = Carbon::now()->subDays($days)->toDateString();
            
            $sales = Sale::where('payment_statut', '!=', 'paid')
                ->where('deleted_at', '=', null)
                ->whereDate('date', $date)
                ->get();

            foreach ($sales as $sale) {
                 $this->info("Invoice {$sale->Ref} is due/overdue ($days days old). Sending notification...");
                 
                 // Send notification to Admin (User ID 1 for now, or fetch all admins)
                 $admin = User::find(1);
                 if ($admin) {
                    $admin->notify(new \App\Notifications\InvoiceDue($sale));
                 }
            }
        }
    }

    protected function checkExpiringProducts()
    {
        // Products expiring in 30, 7, 1 days
        $daysToExpiry = [30, 7, 1];

        foreach ($daysToExpiry as $days) {
            $date = Carbon::now()->addDays($days)->toDateString();

            // Join with products to get expiry_date
            $products = product_warehouse::join('products', 'product_warehouse.product_id', '=', 'products.id')
                ->where('product_warehouse.deleted_at', '=', null)
                ->where('product_warehouse.qte', '>', 0)
                ->whereDate('products.expiry_date', $date)
                ->select('products.name', 'products.code', 'product_warehouse.qte', 'products.expiry_date')
                ->get();

            foreach ($products as $product) {
                $this->info("Product {$product->name} expires in $days days. Sending notification...");
                
                 $admin = User::find(1);
                 if ($admin) {
                     // Pass daysLeft to constructor
                    $admin->notify(new \App\Notifications\ExpiringSoon($product, $days));
                 }
            }
        }
    }
}
