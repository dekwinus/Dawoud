<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Client;
use App\Models\PaymentSale;
use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Unit;
use App\Models\User;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesApprovalController extends BaseController
{
    /**
     * List all pending sales for admin approval.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Only admin (role_id = 1) can access
        if ((int) $user->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->limit ?? 25;
        $pageStart = \Request::get('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;

        $query = Sale::with('client', 'warehouse', 'user', 'details.product')
            ->where('deleted_at', null)
            ->where('approval_status', 'pending');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Ref', 'LIKE', "%{$search}%")
                  ->orWhere('GrandTotal', $search)
                  ->orWhereHas('client', function ($q2) use ($search) {
                      $q2->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('username', 'LIKE', "%{$search}%");
                  });
            });
        }

        $totalRows = $query->count();
        if ($perPage == '-1') {
            $perPage = $totalRows;
        }

        $sales = $query->offset($offSet)
            ->limit($perPage)
            ->orderBy('id', 'DESC')
            ->get();

        $data = [];
        foreach ($sales as $sale) {
            $item = [];
            $item['id'] = $sale->id;
            $item['date'] = $sale->date . ' ' . $sale->time;
            $item['Ref'] = $sale->Ref;
            $item['client_name'] = $sale->client ? $sale->client->name : '—';
            $item['client_id'] = $sale->client_id;
            $item['warehouse_name'] = $sale->warehouse ? $sale->warehouse->name : '—';
            $item['GrandTotal'] = number_format($sale->GrandTotal, 2, '.', '');
            $item['created_by'] = $sale->user ? $sale->user->username : '—';
            $item['is_pos'] = $sale->is_pos;
            $item['approval_status'] = $sale->approval_status;
            $item['notes'] = $sale->notes;

            // Sale details (products)
            $products = [];
            foreach ($sale->details as $detail) {
                $products[] = [
                    'product_name' => $detail->product ? $detail->product->name : '—',
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                    'total' => $detail->total,
                ];
            }
            $item['products'] = $products;

            $data[] = $item;
        }

        return response()->json([
            'totalRows' => $totalRows,
            'sales' => $data,
        ]);
    }

    /**
     * Show a single pending sale with full details.
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        if ((int) $user->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sale = Sale::with('client', 'warehouse', 'user', 'details.product', 'facture.payment_method')
            ->where('deleted_at', null)
            ->findOrFail($id);

        $details = [];
        foreach ($sale->details as $detail) {
            $details[] = [
                'id' => $detail->id,
                'product_name' => $detail->product ? $detail->product->name : '—',
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'price' => $detail->price,
                'total' => $detail->total,
                'discount' => $detail->discount,
                'TaxNet' => $detail->TaxNet,
                'sale_unit_id' => $detail->sale_unit_id,
                'product_variant_id' => $detail->product_variant_id,
            ];
        }

        $payments = [];
        foreach ($sale->facture as $payment) {
            $payments[] = [
                'id' => $payment->id,
                'montant' => $payment->montant,
                'Ref' => $payment->Ref,
                'payment_method' => $payment->payment_method ? $payment->payment_method->name : '—',
                'account_id' => $payment->account_id,
                'status' => $payment->status,
            ];
        }

        return response()->json([
            'sale' => [
                'id' => $sale->id,
                'Ref' => $sale->Ref,
                'date' => $sale->date . ' ' . $sale->time,
                'client_name' => $sale->client ? $sale->client->name : '—',
                'warehouse_name' => $sale->warehouse ? $sale->warehouse->name : '—',
                'GrandTotal' => $sale->GrandTotal,
                'created_by' => $sale->user ? $sale->user->username : '—',
                'approval_status' => $sale->approval_status,
                'notes' => $sale->notes,
                'is_pos' => $sale->is_pos,
                'discount' => $sale->discount,
                'shipping' => $sale->shipping,
                'TaxNet' => $sale->TaxNet,
                'tax_rate' => $sale->tax_rate,
            ],
            'details' => $details,
            'payments' => $payments,
        ]);
    }

    /**
     * Approve a pending sale — process stock, payments, and loyalty.
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if ((int) $user->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sale = Sale::with('details', 'facture')
            ->where('deleted_at', null)
            ->where('approval_status', 'pending')
            ->findOrFail($id);

        try {
            \DB::transaction(function () use ($sale, $user) {
                // 1) STOCK DEDUCTION
                foreach ($sale->details as $detail) {
                    $product = Product::find($detail->product_id);
                    if (!$product) continue;

                    $isService = $product->type === 'is_service';
                    if ($isService) continue;

                    // Resolve unit
                    $unit = null;
                    if ($detail->sale_unit_id) {
                        $unit = Unit::find($detail->sale_unit_id);
                    }
                    if (!$unit) {
                        $productWithUnit = Product::with('unitSale')->find($detail->product_id);
                        if ($productWithUnit && $productWithUnit->unitSale) {
                            $unit = $productWithUnit->unitSale;
                        }
                    }
                    if (!$unit) continue;

                    if ($detail->product_variant_id) {
                        $pw = product_warehouse::where('warehouse_id', $sale->warehouse_id)
                            ->where('product_id', $detail->product_id)
                            ->where('product_variant_id', $detail->product_variant_id)
                            ->first();
                    } else {
                        $pw = product_warehouse::where('warehouse_id', $sale->warehouse_id)
                            ->where('product_id', $detail->product_id)
                            ->first();
                    }

                    if ($pw) {
                        if ($unit->operator == '/') {
                            $pw->qte -= $detail->quantity / $unit->operator_value;
                        } else {
                            $pw->qte -= $detail->quantity * $unit->operator_value;
                        }
                        $pw->save();
                    }
                }

                // 2) PROCESS PAYMENTS (update account balances, mark as approved)
                $totalPaid = 0;
                foreach ($sale->facture as $payment) {
                    if ($payment->status === 'pending') {
                        $payment->update([
                            'status' => 'approved',
                            'approved_by' => $user->id,
                            'approved_at' => Carbon::now(),
                        ]);

                        // Update account balance
                        if ($payment->account_id) {
                            $account = Account::find($payment->account_id);
                            if ($account) {
                                $account->update(['balance' => $account->balance + $payment->montant]);
                            }
                        }
                    }
                    $totalPaid += $payment->montant;
                }

                // 3) LOYALTY POINTS
                $client = Client::find($sale->client_id);
                $total_points_earned = 0;

                foreach ($sale->details as $detail) {
                    $product = Product::find($detail->product_id);
                    if ($product) {
                        $total_points_earned += $detail->quantity * $product->points;
                    }
                }

                $used_points = 0;
                $earned_points = 0;
                $discount_from_points = 0;

                if ($client && ($client->is_royalty_eligible == 1)) {
                    $earned_points = $total_points_earned;
                    $client->increment('points', $earned_points);
                }

                // 4) UPDATE SALE STATUS
                $totalPaidAdjusted = min($totalPaid, $sale->GrandTotal);
                $due = $sale->GrandTotal - $totalPaidAdjusted;

                $sale->update([
                    'statut' => 'completed',
                    'approval_status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => Carbon::now(),
                    'paid_amount' => $totalPaidAdjusted,
                    'payment_statut' => $due <= 0 ? 'paid' : ($due < $sale->GrandTotal ? 'partial' : 'unpaid'),
                    'earned_points' => $earned_points,
                ]);

            }, 10);

            return response()->json([
                'success' => true,
                'message' => 'Sale approved successfully',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a pending sale — no stock/payment changes, mark as rejected.
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if ((int) $user->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sale = Sale::with('facture')
            ->where('deleted_at', null)
            ->where('approval_status', 'pending')
            ->findOrFail($id);

        try {
            \DB::transaction(function () use ($sale, $user) {
                // Mark payments as rejected
                foreach ($sale->facture as $payment) {
                    if ($payment->status === 'pending') {
                        $payment->update([
                            'status' => 'rejected',
                        ]);
                    }
                }

                // Mark sale as rejected
                $sale->update([
                    'statut' => 'rejected',
                    'approval_status' => 'rejected',
                    'approved_by' => $user->id,
                    'approved_at' => Carbon::now(),
                ]);
            }, 10);

            return response()->json([
                'success' => true,
                'message' => 'Sale rejected successfully',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get count of pending sales (for badge/notification).
     */
    public function pendingCount()
    {
        $count = Sale::where('deleted_at', null)
            ->where('approval_status', 'pending')
            ->count();

        return response()->json(['count' => $count]);
    }
}
