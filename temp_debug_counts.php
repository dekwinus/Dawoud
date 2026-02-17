<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;
use App\Models\product_warehouse;
use Carbon\Carbon;

echo "--- Debugging Notification Counts ---\n";

// 1. Invoice Due Soon Logic
// Logic from UserController: whereDate('date', '<=', \Carbon\Carbon::now()->subDays(14))
// This means "Unpaid Sales OLDER than or EQUAL TO 14 days ago"
$dateThreshold = Carbon::now()->subDays(14)->toDateString();
echo "Invoice Date Threshold (<=): " . $dateThreshold . "\n";

$invoice_due_count = Sale::where('deleted_at', '=', null)
    ->where('payment_statut', '!=', 'paid')
    ->whereDate('date', '<=', $dateThreshold)
    ->count();

echo "Invoice Due Count: " . $invoice_due_count . "\n";

if ($invoice_due_count == 0) {
    echo "Check: Are there any unpaid sales at all?\n";
    $unpaid_sales = Sale::where('payment_statut', '!=', 'paid')->get();
    echo "Total Unpaid Sales: " . $unpaid_sales->count() . "\n";
    if ($unpaid_sales->count() > 0) {
        foreach($unpaid_sales->take(5) as $sale) {
            echo " - Sale {$sale->Ref}: Date={$sale->date}, Payment Status={$sale->payment_statut}\n";
        }
    }
}


// 2. Expiry Alert Logic
// Logic from UserController: 
// ->whereDate('products.expiry_date', '<=', \Carbon\Carbon::now()->addDays(30))
// ->whereDate('products.expiry_date', '>=', \Carbon\Carbon::now())

$expiryStart = Carbon::now()->toDateString();
$expiryEnd = Carbon::now()->addDays(30)->toDateString();

echo "\nExpiry Window: $expiryStart to $expiryEnd\n";

$expiry_alert_count = product_warehouse::join('products', 'product_warehouse.product_id', '=', 'products.id')
    ->where('product_warehouse.deleted_at', '=', null)
    ->where('product_warehouse.qte', '>', 0)
    ->whereNotNull('products.expiry_date')
    ->whereDate('products.expiry_date', '<=', $expiryEnd)
    ->whereDate('products.expiry_date', '>=', $expiryStart)
    ->count();

echo "Expiry Alert Count: " . $expiry_alert_count . "\n";

if ($expiry_alert_count == 0) {
    echo "Check: Are there any products with expiry dates?\n";
    $products_with_expiry = product_warehouse::join('products', 'product_warehouse.product_id', '=', 'products.id')
        ->whereNotNull('products.expiry_date')
        ->select('products.name', 'products.expiry_date', 'product_warehouse.qte')
        ->orderBy('products.expiry_date', 'asc')
        ->take(5)
        ->get();
    
    if ($products_with_expiry->count() > 0) {
        foreach($products_with_expiry as $p) {
            echo " - Product {$p->name}: Expiry={$p->expiry_date}, Qty={$p->qte}\n";
        }
    } else {
        echo "No products found with expiry dates.\n";
    }
}
