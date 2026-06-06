<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\OnlineOrder;
use App\Models\OnlineOrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function checkout()
    {
        $s = StoreSetting::firstOrFail();
        $u = Auth::guard('store')->user();

        return Inertia::render('Store/Checkout', [
            's' => $s,
            'client' => $u ? $u->client : null,
        ]);
    }

    public function thankyou(Request $request)
    {
        $s = StoreSetting::firstOrFail();
        $user = Auth::guard('store')->user();
        $order = null;

        if ($request->filled('order') && $user) {
            $onlineOrder = OnlineOrder::with(['items.product', 'items.productVariant'])
                ->whereKey($request->integer('order'))
                ->where('client_id', $user->client_id)
                ->first();

            if ($onlineOrder) {
                $order = [
                    'id' => $onlineOrder->id,
                    'ref' => $onlineOrder->ref,
                    'status' => $onlineOrder->status,
                    'date' => optional($onlineOrder->date)->toDateString() ?: (string) $onlineOrder->date,
                    'time' => (string) $onlineOrder->time,
                    'total' => (float) $onlineOrder->total,
                    'items' => $onlineOrder->items->map(function (OnlineOrderItem $item) {
                        $productName = optional($item->product)->name ?? ('#'.$item->product_id);
                        $variantName = optional($item->productVariant)->name;

                        return [
                            'id' => $item->id,
                            'name' => $variantName ? $productName.' - '.$variantName : $productName,
                            'qty' => (float) $item->qty,
                            'price' => (float) $item->price,
                            'line_total' => (float) $item->line_total,
                            'image' => optional($item->product)->image,
                        ];
                    })->values(),
                ];
            }
        }

        return Inertia::render('Store/ThankYou', [
            's' => $s,
            'order' => $order,
        ]);
    }

    public function store(Request $req)
    {
        // Logged-in ecommerce client (guard: store)
        $user = Auth::guard('store')->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Prices, discounts, and taxes are always resolved from the database.
        $data = $req->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.product_variant_id' => ['nullable', 'integer'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'warehouse_id' => ['nullable', 'integer'],
        ]);

        // Resolve warehouse: request → settings.default_warehouse_id → first warehouse
        $warehouseId = (int) ($data['warehouse_id'] ?? 0);
        if (! $warehouseId) {
            $warehouseId = (int) DB::table('store_settings')->value('default_warehouse_id');
        }
        if ($warehouseId && ! Warehouse::whereKey($warehouseId)->exists()) {
            $warehouseId = 0;
        }
        if (! $warehouseId) {
            $warehouseId = (int) Warehouse::value('id');
        }
        if (! $warehouseId) {
            return response()->json(['error' => 'No warehouse configured.'], 422);
        }

        // Preload product meta (TaxNet/discount/flags) and verify all exist
        $ids = collect($data['items'])->pluck('product_id')->unique()->values();
        $products = Product::with('variants')
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('hide_from_online_store', 0)
            ->get()
            ->keyBy('id');

        if ($products->count() !== $ids->count()) {
            $missing = $ids->diff($products->keys());

            return response()->json([
                'error' => 'Some products not found.',
                'product_ids' => $missing->values(),
            ], 422);
        }

        // Normalize items & totals
        $normalizedItems = [];
        $subtotal = 0.0;

        foreach ($data['items'] as $i) {
            $pid = (int) $i['product_id'];
            $pvid = ! empty($i['product_variant_id']) ? (int) $i['product_variant_id'] : null;
            $meta = $products->get($pid);
            $variant = $pvid ? $meta->variants->firstWhere('id', $pvid) : null;

            if ($pvid && ! $variant) {
                return response()->json([
                    'error' => 'The selected variant does not belong to this product.',
                    'product_id' => $pid,
                    'product_variant_id' => $pvid,
                ], 422);
            }

            // Quick-add cards show the cheapest variant. Resolve that exact variant
            // when the client did not explicitly select one.
            if (! $variant && $meta->is_variant && $meta->variants->isNotEmpty()) {
                $variant = $meta->variants->sortBy('price')->first();
                $pvid = $variant->id;
            }

            $qty = max(0.001, (float) $i['qty']);
            $basePrice = $variant ? (float) $variant->price : (float) $meta->price;
            $price = (float) $meta->computeFinalPrice(null, $basePrice)['final'];
            $line = round($qty * $price, 2);

            $normalizedItems[] = [
                'product_id' => $pid,
                'product_variant_id' => $pvid,
                'qty' => $qty,
                'price' => $price,

                'TaxNet' => (float) ($meta->TaxNet ?? 0),
                'discount' => (float) ($meta->discount ?? 0),
                'discount_method' => (string) ($meta->discount_method ?? '1'),
                'tax_method' => (string) ($meta->tax_method ?? '1'),

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $subtotal += $line;
        }

        $grand = round(max(0, $subtotal), 2);
        $clientId = $user->client_id ?? null;
        if (! $clientId) {
            return response()->json([
                'error' => 'Your store account is not linked to a customer record.',
            ], 422);
        }

        // Explicit date/time/status/ref on create
        $todayDate = now()->toDateString();
        $nowTime = now()->format('H:i:s');
        $ref = method_exists(\App\Models\OnlineOrder::class, 'generateRef')
                    ? \App\Models\OnlineOrder::generateRef()
                    : ('SO-'.now()->format('Ymd').'-'.str_pad((string) ((\App\Models\OnlineOrder::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT));

        $order = DB::transaction(function () use ($clientId, $warehouseId, $grand, $normalizedItems, $todayDate, $nowTime, $ref) {
            $order = \App\Models\OnlineOrder::create([
                'date' => $todayDate,
                'time' => $nowTime,
                'ref' => $ref,
                'status' => 'pending',
                'client_id' => $clientId,
                'warehouse_id' => $warehouseId,
                'total' => $grand,
            ]);

            $order->items()->createMany($normalizedItems); // OnlineOrderItem boot() will set line_total

            return $order;
        });

        return response()->json([
            'id' => $order->id,
            'ref' => $order->ref,
            'status' => $order->status,
            'date' => (string) $order->date,
            'time' => (string) $order->time,
            'total' => (float) $order->total,
        ], 201);
    }
}
