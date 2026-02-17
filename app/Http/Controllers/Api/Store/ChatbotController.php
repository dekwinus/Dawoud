<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CompetitorProduct;
use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\StoreSetting;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    /**
     * Show the chatbot page (no auth required).
     */
    public function index()
    {
        $s = StoreSetting::first() ?? new StoreSetting();
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);

        return view('store.chatbot', [
            's' => $s,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    /**
     * Handle chatbot messages and return intelligent responses.
     */
    public function message(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'nullable|array',
        ]);

        $userMessage = trim($request->input('message'));
        $context = $request->input('context', []);

        $response = $this->processMessage($userMessage, $context);

        return response()->json($response);
    }

    /**
     * Provide quick data for autocomplete / suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);

        $priceRange = Product::where('is_active', 1)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
            'price_range' => [
                'min' => (float) ($priceRange->min_price ?? 0),
                'max' => (float) ($priceRange->max_price ?? 0),
            ],
        ]);
    }

    /**
     * Search products via chatbot query.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'nullable|string|max:500',
            'category_id' => 'nullable|integer',
            'brand_id' => 'nullable|integer',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'in_stock' => 'nullable|boolean',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = $request->input('query', '');
        $categoryId = $request->input('category_id');
        $brandId = $request->input('brand_id');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $inStock = $request->input('in_stock', false);
        $limit = $request->input('limit', 6);

        $products = Product::query()
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('hide_from_online_store', 0)
                  ->orWhereNull('hide_from_online_store');
            })
            ->with(['category:id,name', 'brand:id,name', 'variants:id,product_id,name,price,image'])
            ->when($query, function ($qb) use ($query) {
                $qb->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhere('note', 'like', "%{$query}%");
                });
            })
            ->when($categoryId, fn ($qb) => $qb->where('category_id', $categoryId))
            ->when($brandId, fn ($qb) => $qb->where('brand_id', $brandId))
            ->when(is_numeric($minPrice), fn ($qb) => $qb->where('price', '>=', (float) $minPrice))
            ->when(is_numeric($maxPrice), fn ($qb) => $qb->where('price', '<=', (float) $maxPrice))
            ->when($inStock, function ($qb) {
                $qb->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('product_warehouse')
                        ->whereColumn('product_warehouse.product_id', 'products.id')
                        ->where('product_warehouse.qte', '>', 0);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $results = $products->map(function ($p) {
            $stock = product_warehouse::where('product_id', $p->id)->sum('qte');
            $finalPrice = $p->computeFinalPrice();

            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'image' => $p->image ? asset("images/products/{$p->image}") : asset('images/products/no-image.png'),
                'price' => $finalPrice['final'],
                'original_price' => $finalPrice['base'],
                'has_discount' => $finalPrice['discount'] > 0,
                'discount_amount' => $finalPrice['discount'],
                'category' => $p->category->name ?? null,
                'brand' => $p->brand->name ?? null,
                'in_stock' => $stock > 0,
                'stock_qty' => $stock,
                'note' => $p->note,
                'has_variants' => (bool) $p->is_variant,
                'variants' => $p->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'price' => (float) $v->price,
                ])->toArray(),
            ];
        });

        return response()->json([
            'products' => $results,
            'count' => $results->count(),
        ]);
    }

    /**
     * Compare our product with competitor products.
     */
    public function compareProducts(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'nullable|integer',
            'query' => 'nullable|string|max:500',
            'category_id' => 'nullable|integer',
        ]);

        $productId = $request->input('product_id');
        $query = $request->input('query', '');
        $categoryId = $request->input('category_id');

        $ourProduct = null;
        if ($productId) {
            $ourProduct = Product::with(['category:id,name', 'brand:id,name'])->find($productId);
        }

        // Find competitor products
        $competitors = CompetitorProduct::query()
            ->where('is_active', true)
            ->when($categoryId, fn ($qb) => $qb->where('category_id', $categoryId))
            ->when($ourProduct, fn ($qb) => $qb->where('category_id', $ourProduct->category_id))
            ->when($query, function ($qb) use ($query) {
                $qb->where(function ($sub) use ($query) {
                    $sub->where('product_name', 'like', "%{$query}%")
                        ->orWhere('competitor_name', 'like', "%{$query}%");
                });
            })
            ->with(['category:id,name', 'brand:id,name'])
            ->limit(10)
            ->get();

        $result = [
            'our_product' => null,
            'competitors' => $competitors->map(fn ($c) => [
                'id' => $c->id,
                'competitor_name' => $c->competitor_name,
                'product_name' => $c->product_name,
                'price' => $c->price,
                'description' => $c->description,
                'category' => $c->category->name ?? null,
                'brand' => $c->brand->name ?? null,
            ])->toArray(),
        ];

        if ($ourProduct) {
            $finalPrice = $ourProduct->computeFinalPrice();
            $stock = product_warehouse::where('product_id', $ourProduct->id)->sum('qte');

            $result['our_product'] = [
                'id' => $ourProduct->id,
                'name' => $ourProduct->name,
                'price' => $finalPrice['final'],
                'original_price' => $finalPrice['base'],
                'category' => $ourProduct->category->name ?? null,
                'brand' => $ourProduct->brand->name ?? null,
                'in_stock' => $stock > 0,
            ];
        }

        return response()->json($result);
    }

    /**
     * Process user message and generate intelligent response.
     */
    private function processMessage(string $message, array $context): array
    {
        $lower = mb_strtolower($message);
        $s = StoreSetting::first();
        $currency = $s->currency_code ?? '$';

        // Detect intent
        $intent = $this->detectIntent($lower);

        switch ($intent) {
            case 'greeting':
                return $this->greetingResponse($s);

            case 'search_product':
                return $this->searchProductResponse($lower, $context, $currency);

            case 'check_stock':
                return $this->checkStockResponse($lower, $currency);

            case 'price_inquiry':
                return $this->priceInquiryResponse($lower, $currency);

            case 'category_browse':
                return $this->categoryBrowseResponse($lower, $currency);

            case 'recommendation':
                return $this->recommendationResponse($lower, $context, $currency);

            case 'compare':
                return $this->compareResponse($lower, $currency);

            case 'help':
                return $this->helpResponse();

            default:
                return $this->defaultResponse($lower, $context, $currency);
        }
    }

    private function detectIntent(string $message): string
    {
        $greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'salut', 'bonjour', 'merhaba',
            'مرحبا', 'أهلا', 'السلام عليكم', 'صباح الخير', 'مساء الخير', 'هلا', 'سلام',
        ];
        $stockWords = ['stock', 'available', 'availability', 'in stock', 'out of stock', 'quantity', 'how many',
            'مخزون', 'متوفر', 'متاح', 'توفر', 'غير متوفر', 'كمية', 'كم عدد', 'موجود',
        ];
        $priceWords = ['price', 'cost', 'how much', 'expensive', 'cheap', 'affordable', 'budget',
            'سعر', 'تكلفة', 'كم سعر', 'بكم', 'غالي', 'رخيص', 'ميزانية', 'أسعار', 'ثمن',
        ];
        $categoryWords = ['category', 'categories', 'types', 'what do you sell', 'what products', 'show me',
            'فئة', 'فئات', 'أنواع', 'أصناف', 'ماذا تبيعون', 'ما المنتجات', 'أرني', 'عرض',
        ];
        $recommendWords = ['recommend', 'suggestion', 'suggest', 'best', 'top', 'popular', 'what should', 'which one', 'help me choose', 'looking for', 'i need', 'i want',
            'اقترح', 'توصية', 'نصيحة', 'أفضل', 'الأفضل', 'شعبي', 'ماذا تنصح', 'ساعدني', 'أبحث عن', 'أحتاج', 'أريد', 'اقتراح',
        ];
        $compareWords = ['compare', 'comparison', 'competitor', 'vs', 'versus', 'better than', 'difference',
            'قارن', 'مقارنة', 'منافس', 'منافسين', 'أفضل من', 'الفرق', 'ضد',
        ];
        $helpWords = ['help', 'what can you do', 'commands', 'options', 'menu',
            'مساعدة', 'ماذا يمكنك', 'أوامر', 'خيارات', 'قائمة',
        ];
        $searchWords = ['search', 'find', 'look for', 'do you have', 'show',
            'بحث', 'ابحث', 'جد', 'هل لديكم', 'عندكم', 'أبحث',
        ];

        foreach ($greetings as $g) {
            if (str_contains($message, $g)) return 'greeting';
        }
        foreach ($helpWords as $w) {
            if (str_contains($message, $w)) return 'help';
        }
        foreach ($compareWords as $w) {
            if (str_contains($message, $w)) return 'compare';
        }
        foreach ($recommendWords as $w) {
            if (str_contains($message, $w)) return 'recommendation';
        }
        foreach ($stockWords as $w) {
            if (str_contains($message, $w)) return 'check_stock';
        }
        foreach ($priceWords as $w) {
            if (str_contains($message, $w)) return 'price_inquiry';
        }
        foreach ($categoryWords as $w) {
            if (str_contains($message, $w)) return 'category_browse';
        }
        foreach ($searchWords as $w) {
            if (str_contains($message, $w)) return 'search_product';
        }

        return 'default';
    }

    private function greetingResponse($s): array
    {
        $storeName = $s->store_name ?? __('messages.Store');

        return [
            'type' => 'text',
            'message' => __('messages.ChatbotGreeting', ['store' => $storeName]),
            'suggestions' => [
                __('messages.ChatbotShowCategories'),
                __('messages.ChatbotRecommendProducts'),
                __('messages.ChatbotCheckStock'),
                __('messages.ChatbotComparePrices'),
            ],
        ];
    }

    private function helpResponse(): array
    {
        return [
            'type' => 'text',
            'message' => __('messages.ChatbotHelpMessage'),
            'suggestions' => [
                __('messages.ChatbotShowCategories'),
                __('messages.ChatbotShowPopular'),
                __('messages.ChatbotShowCheapest'),
                __('messages.ChatbotWhatsInStock'),
            ],
        ];
    }

    private function searchProductResponse(string $message, array $context, string $currency): array
    {
        $keywords = $this->extractKeywords($message);

        $products = Product::query()
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('hide_from_online_store', 0)->orWhereNull('hide_from_online_store');
            })
            ->with(['category:id,name', 'brand:id,name'])
            ->where(function ($qb) use ($keywords) {
                foreach ($keywords as $kw) {
                    $qb->orWhere('name', 'like', "%{$kw}%")
                        ->orWhere('code', 'like', "%{$kw}%")
                        ->orWhere('note', 'like', "%{$kw}%");
                }
            })
            ->limit(6)
            ->get();

        if ($products->isEmpty()) {
            return [
                'type' => 'text',
                'message' => __('messages.ChatbotNoProductsMatch'),
                'suggestions' => [__('messages.ChatbotShowCategories'), __('messages.ChatbotShowAllProducts'), __('messages.ChatbotHelp')],
            ];
        }

        return [
            'type' => 'products',
            'message' => __('messages.ChatbotFoundProducts', ['count' => $products->count()]),
            'products' => $this->formatProducts($products, $currency),
            'suggestions' => [__('messages.ChatbotMoreDetails'), __('messages.ChatbotCheckStock'), __('messages.ChatbotComparePrices')],
        ];
    }

    private function checkStockResponse(string $message, string $currency): array
    {
        $keywords = $this->extractKeywords($message);

        if (empty($keywords)) {
            $lowStock = Product::query()
                ->where('is_active', 1)
                ->with(['category:id,name'])
                ->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('product_warehouse')
                        ->whereColumn('product_warehouse.product_id', 'products.id')
                        ->where('product_warehouse.qte', '>', 0);
                })
                ->limit(6)
                ->get();

            return [
                'type' => 'products',
                'message' => __('messages.ChatbotProductsInStock'),
                'products' => $this->formatProducts($lowStock, $currency),
                'suggestions' => [__('messages.ChatbotShowAllInStock'), __('messages.ChatbotShowCategories')],
            ];
        }

        $products = Product::query()
            ->where('is_active', 1)
            ->with(['category:id,name', 'brand:id,name'])
            ->where(function ($qb) use ($keywords) {
                foreach ($keywords as $kw) {
                    $qb->orWhere('name', 'like', "%{$kw}%");
                }
            })
            ->limit(6)
            ->get();

        if ($products->isEmpty()) {
            return [
                'type' => 'text',
                'message' => __('messages.ChatbotProductNotFound'),
                'suggestions' => [__('messages.ChatbotShowCategories'), __('messages.ChatbotShowAllInStock')],
            ];
        }

        $results = [];
        foreach ($products as $p) {
            $stock = product_warehouse::where('product_id', $p->id)->sum('qte');
            $final = $p->computeFinalPrice();
            $results[] = [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $currency . number_format($final['final'], 2),
                'image' => $p->image ? asset("images/products/{$p->image}") : asset('images/products/no-image.png'),
                'category' => $p->category->name ?? '',
                'in_stock' => $stock > 0,
                'stock_qty' => (int) $stock,
                'stock_label' => $stock > 0 ? __('messages.ChatbotInStockCount', ['count' => (int) $stock]) : __('messages.ChatbotOutOfStock'),
            ];
        }

        return [
            'type' => 'stock',
            'message' => __('messages.ChatbotStockInfo'),
            'products' => $results,
            'suggestions' => [__('messages.ChatbotRecommendAlternatives'), __('messages.ChatbotComparePrices')],
        ];
    }

    private function priceInquiryResponse(string $message, string $currency): array
    {
        // Extract price range from message
        $maxBudget = null;
        $minBudget = null;

        // English patterns
        if (preg_match('/under\s*[\$€£]?\s*(\d+)/i', $message, $m)) {
            $maxBudget = (float) $m[1];
        }
        if (preg_match('/over\s*[\$€£]?\s*(\d+)/i', $message, $m)) {
            $minBudget = (float) $m[1];
        }
        if (preg_match('/between\s*[\$€£]?\s*(\d+)\s*(?:and|to|-)\s*[\$€£]?\s*(\d+)/i', $message, $m)) {
            $minBudget = (float) $m[1];
            $maxBudget = (float) $m[2];
        }
        // Arabic patterns
        if (preg_match('/(?:أقل من|تحت|اقل من)\s*[\$€£]?\s*(\d+)/u', $message, $m)) {
            $maxBudget = (float) $m[1];
        }
        if (preg_match('/(?:أكثر من|فوق|اكثر من)\s*[\$€£]?\s*(\d+)/u', $message, $m)) {
            $minBudget = (float) $m[1];
        }
        if (preg_match('/(?:بين|من)\s*[\$€£]?\s*(\d+)\s*(?:و|إلى|الى|-)\s*[\$€£]?\s*(\d+)/u', $message, $m)) {
            $minBudget = (float) $m[1];
            $maxBudget = (float) $m[2];
        }

        $keywords = $this->extractKeywords($message);

        $qb = Product::query()
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('hide_from_online_store', 0)->orWhereNull('hide_from_online_store');
            })
            ->with(['category:id,name', 'brand:id,name']);

        if ($maxBudget) {
            $qb->where('price', '<=', $maxBudget);
        }
        if ($minBudget) {
            $qb->where('price', '>=', $minBudget);
        }

        if (!empty($keywords)) {
            $qb->where(function ($sub) use ($keywords) {
                foreach ($keywords as $kw) {
                    $sub->orWhere('name', 'like', "%{$kw}%");
                }
            });
        }

        if (str_contains($message, 'cheap') || str_contains($message, 'affordable') || str_contains($message, 'budget')
            || str_contains($message, 'رخيص') || str_contains($message, 'أرخص') || str_contains($message, 'ميزانية')) {
            $qb->orderBy('price', 'asc');
        } else {
            $qb->orderBy('price', 'desc');
        }

        $products = $qb->limit(6)->get();

        if ($products->isEmpty()) {
            return [
                'type' => 'text',
                'message' => __('messages.ChatbotNoPriceRange'),
                'suggestions' => [__('messages.ChatbotShowAllProducts'), __('messages.ChatbotShowCategories'), __('messages.ChatbotShowCheapest')],
            ];
        }

        $label = '';
        if ($maxBudget && $minBudget) {
            $label = __('messages.ChatbotPriceBetween', [
                'min' => $currency . number_format($minBudget, 0),
                'max' => $currency . number_format($maxBudget, 0),
            ]);
        } elseif ($maxBudget) {
            $label = __('messages.ChatbotPriceUnder', ['max' => $currency . number_format($maxBudget, 0)]);
        } elseif ($minBudget) {
            $label = __('messages.ChatbotPriceOver', ['min' => $currency . number_format($minBudget, 0)]);
        }

        return [
            'type' => 'products',
            'message' => $label ? __('messages.ChatbotProductsPriceRange', ['range' => $label]) : __('messages.ChatbotProductsSortedByPrice'),
            'products' => $this->formatProducts($products, $currency),
            'suggestions' => [__('messages.ChatbotShowCheapest'), __('messages.ChatbotComparePrices'), __('messages.ChatbotCheckStock')],
        ];
    }

    private function categoryBrowseResponse(string $message, string $currency): array
    {
        $categories = Category::withCount(['products' => function ($q) {
            $q->where('is_active', 1);
        }])->orderBy('name')->get();

        if ($categories->isEmpty()) {
            return [
                'type' => 'text',
                'message' => __('messages.ChatbotNoCategoriesAvailable'),
                'suggestions' => [__('messages.ChatbotShowAllProducts'), __('messages.ChatbotHelp')],
            ];
        }

        // Check if they're asking for a specific category
        $matchedCat = null;
        foreach ($categories as $cat) {
            if (str_contains($message, mb_strtolower($cat->name))) {
                $matchedCat = $cat;
                break;
            }
        }

        if ($matchedCat) {
            $products = Product::where('is_active', 1)
                ->where('category_id', $matchedCat->id)
                ->with(['brand:id,name'])
                ->limit(6)
                ->get();

            return [
                'type' => 'products',
                'message' => __('messages.ChatbotProductsInCategory', ['category' => $matchedCat->name]),
                'products' => $this->formatProducts($products, $currency),
                'suggestions' => [__('messages.ChatbotShowCategories'), __('messages.ChatbotCheckStock'), __('messages.ChatbotRecommendBest')],
            ];
        }

        $catList = $categories->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'product_count' => $c->products_count ?? 0,
        ])->toArray();

        return [
            'type' => 'categories',
            'message' => __('messages.ChatbotBrowseCategories'),
            'categories' => $catList,
            'suggestions' => [__('messages.ChatbotRecommendProducts'), __('messages.ChatbotShowPopular'), __('messages.ChatbotHelp')],
        ];
    }

    private function recommendationResponse(string $message, array $context, string $currency): array
    {
        // Extract budget if mentioned
        $maxBudget = null;
        if (preg_match('/under\s*[\$€£]?\s*(\d+)/i', $message, $m)) {
            $maxBudget = (float) $m[1];
        }
        if (preg_match('/(\d+)\s*(?:budget|max)/i', $message, $m)) {
            $maxBudget = (float) $m[1];
        }
        // Arabic budget patterns
        if (preg_match('/(?:أقل من|تحت|اقل من)\s*[\$€£]?\s*(\d+)/u', $message, $m)) {
            $maxBudget = (float) $m[1];
        }
        if (preg_match('/(\d+)\s*(?:ميزانية|حد أقصى)/u', $message, $m)) {
            $maxBudget = (float) $m[1];
        }

        // Extract category from context or message
        $categoryId = $context['category_id'] ?? null;
        if (!$categoryId) {
            $cats = Category::all();
            foreach ($cats as $cat) {
                if (str_contains($message, mb_strtolower($cat->name))) {
                    $categoryId = $cat->id;
                    break;
                }
            }
        }

        $qb = Product::query()
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('hide_from_online_store', 0)->orWhereNull('hide_from_online_store');
            })
            ->with(['category:id,name', 'brand:id,name'])
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($maxBudget, fn ($q) => $q->where('price', '<=', $maxBudget));

        // Prefer featured/discounted products
        if (str_contains($message, 'best') || str_contains($message, 'top') || str_contains($message, 'popular')
            || str_contains($message, 'أفضل') || str_contains($message, 'الأفضل') || str_contains($message, 'شعبي')) {
            $qb->orderByRaw('COALESCE(is_featured, 0) DESC')
               ->orderByRaw('COALESCE(discount, 0) DESC');
        } else {
            $qb->orderBy('created_at', 'desc');
        }

        // Only recommend in-stock items
        $qb->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('product_warehouse')
                ->whereColumn('product_warehouse.product_id', 'products.id')
                ->where('product_warehouse.qte', '>', 0);
        });

        $products = $qb->limit(6)->get();

        if ($products->isEmpty()) {
            // Fallback: show any products without stock filter
            $products = Product::query()
                ->where('is_active', 1)
                ->with(['category:id,name', 'brand:id,name'])
                ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
                ->when($maxBudget, fn ($q) => $q->where('price', '<=', $maxBudget))
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        }

        if ($products->isEmpty()) {
            return [
                'type' => 'text',
                'message' => __('messages.ChatbotNoRecommendations'),
                'suggestions' => [__('messages.ChatbotShowAllProducts'), __('messages.ChatbotShowCategories'), __('messages.ChatbotHelp')],
            ];
        }

        $msg = $maxBudget
            ? __('messages.ChatbotTopRecommendationsBudget', ['currency' => $currency, 'budget' => number_format($maxBudget, 0)])
            : __('messages.ChatbotTopRecommendations');

        return [
            'type' => 'products',
            'message' => $msg,
            'products' => $this->formatProducts($products, $currency),
            'suggestions' => [__('messages.ChatbotMoreRecommendations'), __('messages.ChatbotCompareWithCompetitors'), __('messages.ChatbotCheckStock')],
        ];
    }

    private function compareResponse(string $message, string $currency): array
    {
        $keywords = $this->extractKeywords($message);

        // Find our products matching keywords
        $ourProducts = Product::query()
            ->where('is_active', 1)
            ->with(['category:id,name', 'brand:id,name'])
            ->when(!empty($keywords), function ($qb) use ($keywords) {
                $qb->where(function ($sub) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $sub->orWhere('name', 'like', "%{$kw}%");
                    }
                });
            })
            ->limit(3)
            ->get();

        // Find competitor products
        $competitors = CompetitorProduct::query()
            ->where('is_active', true)
            ->with(['category:id,name'])
            ->when(!empty($keywords), function ($qb) use ($keywords) {
                $qb->where(function ($sub) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $sub->orWhere('product_name', 'like', "%{$kw}%")
                            ->orWhere('competitor_name', 'like', "%{$kw}%");
                    }
                });
            })
            ->limit(5)
            ->get();

        if ($ourProducts->isEmpty() && $competitors->isEmpty()) {
            return [
                'type' => 'text',
                'message' => __('messages.ChatbotNoCompareProducts'),
                'suggestions' => [__('messages.ChatbotSearchProducts'), __('messages.ChatbotShowCategories'), __('messages.ChatbotHelp')],
            ];
        }

        $comparison = [
            'our_products' => $ourProducts->map(function ($p) use ($currency) {
                $final = $p->computeFinalPrice();
                $stock = product_warehouse::where('product_id', $p->id)->sum('qte');
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $currency . number_format($final['final'], 2),
                    'price_raw' => $final['final'],
                    'category' => $p->category->name ?? '',
                    'brand' => $p->brand->name ?? '',
                    'in_stock' => $stock > 0,
                    'image' => $p->image ? asset("images/products/{$p->image}") : asset('images/products/no-image.png'),
                ];
            })->toArray(),
            'competitors' => $competitors->map(fn ($c) => [
                'competitor_name' => $c->competitor_name,
                'product_name' => $c->product_name,
                'price' => $currency . number_format($c->price, 2),
                'price_raw' => $c->price,
                'category' => $c->category->name ?? '',
            ])->toArray(),
        ];

        $msg = $competitors->isNotEmpty()
            ? __('messages.ChatbotComparisonWithCompetitors')
            : __('messages.ChatbotComparisonResult');

        return [
            'type' => 'comparison',
            'message' => $msg,
            'comparison' => $comparison,
            'suggestions' => [__('messages.ChatbotRecommendBestValue'), __('messages.ChatbotCheckStock'), __('messages.ChatbotShowMoreProducts')],
        ];
    }

    private function defaultResponse(string $message, array $context, string $currency): array
    {
        // Try a general product search as fallback
        $keywords = $this->extractKeywords($message);

        if (!empty($keywords)) {
            $products = Product::query()
                ->where('is_active', 1)
                ->with(['category:id,name', 'brand:id,name'])
                ->where(function ($qb) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $qb->orWhere('name', 'like', "%{$kw}%")
                            ->orWhere('code', 'like', "%{$kw}%")
                            ->orWhere('note', 'like', "%{$kw}%");
                    }
                })
                ->limit(6)
                ->get();

            if ($products->isNotEmpty()) {
                return [
                    'type' => 'products',
                    'message' => __('messages.ChatbotDefaultSearchResults'),
                    'products' => $this->formatProducts($products, $currency),
                    'suggestions' => [__('messages.ChatbotMoreDetails'), __('messages.ChatbotCheckStock'), __('messages.ChatbotComparePrices')],
                ];
            }
        }

        return [
            'type' => 'text',
            'message' => __('messages.ChatbotDefaultMessage'),
            'suggestions' => [__('messages.ChatbotShowCategories'), __('messages.ChatbotRecommendProducts'), __('messages.ChatbotCheckStock'), __('messages.ChatbotHelp')],
        ];
    }

    /**
     * Format product collection for response.
     */
    private function formatProducts($products, string $currency): array
    {
        return $products->map(function ($p) use ($currency) {
            $final = $p->computeFinalPrice();
            $stock = product_warehouse::where('product_id', $p->id)->sum('qte');

            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'price' => $currency . number_format($final['final'], 2),
                'price_raw' => $final['final'],
                'original_price' => $final['base'] != $final['final'] ? $currency . number_format($final['base'], 2) : null,
                'has_discount' => $final['discount'] > 0,
                'image' => $p->image ? asset("images/products/{$p->image}") : asset('images/products/no-image.png'),
                'category' => $p->category->name ?? '',
                'brand' => $p->brand->name ?? '',
                'in_stock' => $stock > 0,
                'stock_qty' => (int) $stock,
                'note' => $p->note,
            ];
        })->toArray();
    }

    /**
     * Extract meaningful keywords from message (removing stopwords).
     */
    private function extractKeywords(string $message): array
    {
        $stopwords = [
            // English
            'i', 'me', 'my', 'the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been',
            'do', 'does', 'did', 'have', 'has', 'had', 'will', 'would', 'could', 'should',
            'can', 'may', 'might', 'shall', 'to', 'of', 'in', 'for', 'on', 'with', 'at',
            'by', 'from', 'as', 'into', 'about', 'like', 'through', 'after', 'before',
            'between', 'under', 'over', 'above', 'below', 'and', 'but', 'or', 'not', 'no',
            'so', 'if', 'then', 'than', 'that', 'this', 'it', 'its', 'what', 'which',
            'who', 'whom', 'how', 'when', 'where', 'why', 'all', 'each', 'every', 'both',
            'few', 'more', 'most', 'some', 'any', 'such', 'only', 'same', 'just', 'also',
            'very', 'too', 'quite', 'rather', 'please', 'show', 'find', 'search', 'look',
            'get', 'want', 'need', 'give', 'tell', 'check', 'stock', 'price', 'cost',
            'recommend', 'suggest', 'best', 'top', 'popular', 'compare', 'available',
            'product', 'products', 'item', 'items', 'you', 'your', 'there', 'here',
            'much', 'many',
            // Arabic
            'في', 'من', 'على', 'إلى', 'عن', 'مع', 'هل', 'ما', 'هذا', 'هذه', 'ذلك',
            'التي', 'الذي', 'أن', 'أنا', 'أنت', 'هو', 'هي', 'نحن', 'هم', 'كل', 'بعض',
            'لا', 'نعم', 'أو', 'و', 'لكن', 'إذا', 'ثم', 'قد', 'لي', 'لك', 'له', 'لها',
            'كان', 'كانت', 'يكون', 'تكون', 'هناك', 'هنا', 'أي', 'فقط', 'أيضا', 'جدا',
            'من فضلك', 'أرني', 'ابحث', 'جد', 'تحقق', 'مخزون', 'سعر', 'تكلفة',
            'اقترح', 'أفضل', 'قارن', 'متوفر', 'منتج', 'منتجات',
        ];

        $words = preg_split('/[\s,.\-!?]+/', $message);
        $keywords = [];

        foreach ($words as $w) {
            $w = trim($w);
            if (strlen($w) >= 2 && !in_array($w, $stopwords)) {
                $keywords[] = $w;
            }
        }

        return array_values(array_unique($keywords));
    }
}
