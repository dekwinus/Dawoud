<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\product_warehouse;
use Illuminate\Database\Seeder;

class AgriculturalProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Ensure Categories exist
        $categories = [
            ['name' => 'بذور محاصيل', 'code' => 'SEEDS'],
            ['name' => 'أعلاف وتسمين', 'code' => 'FODDER'],
            ['name' => 'أسمدة زراعية', 'code' => 'FERT'],
            ['name' => 'مبيدات زراعية', 'code' => 'CHEM'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['code' => $cat['code']], $cat);
        }

        // 2. Ensure Units exist
        $units = [
            ['name' => 'كيلو جرام', 'ShortName' => 'كجم', 'base_unit' => null, 'operator' => '*', 'operator_value' => 1],
            ['name' => 'طن', 'ShortName' => 'طن', 'base_unit' => null, 'operator' => '*', 'operator_value' => 1000],
            ['name' => 'شكارة 50كجم', 'ShortName' => 'شكارة', 'base_unit' => null, 'operator' => '*', 'operator_value' => 50],
        ];

        foreach ($units as $u) {
            Unit::updateOrCreate(['name' => $u['name']], $u);
        }

        $unit_kg = Unit::where('ShortName', 'كجم')->first();
        $unit_ton = Unit::where('ShortName', 'طن')->first();
        $unit_bag = Unit::where('ShortName', 'شكارة')->first();

        $cat_seeds = Category::where('code', 'SEEDS')->first();
        $cat_fodder = Category::where('code', 'FODDER')->first();
        $cat_fert = Category::where('code', 'FERT')->first();
        $cat_chem = Category::where('code', 'CHEM')->first();

        $warehouse = Warehouse::first() ?: Warehouse::create(['name' => 'المخزن الرئيسي', 'city' => 'المنصورة']);

        $products = [
            // Seeds
            ['name' => 'بذور ذرة صفراء هجين 3080', 'code' => 'S001', 'category_id' => $cat_seeds->id, 'unit_id' => $unit_bag->id, 'cost' => 1200, 'price' => 1500],
            ['name' => 'بذور قمح سدس 12', 'code' => 'S002', 'category_id' => $cat_seeds->id, 'unit_id' => $unit_bag->id, 'cost' => 800, 'price' => 1000],
            ['name' => 'بذور برسيم حجازي ممتاز', 'code' => 'S003', 'category_id' => $cat_seeds->id, 'unit_id' => $unit_kg->id, 'cost' => 150, 'price' => 200],
            ['name' => 'تقاوي بطاطس سبونتا', 'code' => 'S004', 'category_id' => $cat_seeds->id, 'unit_id' => $unit_ton->id, 'cost' => 25000, 'price' => 30000],
            ['name' => 'بذور كتان بلدي', 'code' => 'S005', 'category_id' => $cat_seeds->id, 'unit_id' => $unit_kg->id, 'cost' => 80, 'price' => 120],

            // Fodder
            ['name' => 'علف بادي 23% بروتين', 'code' => 'F001', 'category_id' => $cat_fodder->id, 'unit_id' => $unit_ton->id, 'cost' => 18000, 'price' => 20000],
            ['name' => 'علف نامي 21% بروتين', 'code' => 'F002', 'category_id' => $cat_fodder->id, 'unit_id' => $unit_ton->id, 'cost' => 17500, 'price' => 19500],
            ['name' => 'علف ناهي 19% بروتين', 'code' => 'F003', 'category_id' => $cat_fodder->id, 'unit_id' => $unit_ton->id, 'cost' => 17000, 'price' => 19000],
            ['name' => 'رده خشنه (نخالة)', 'code' => 'F004', 'category_id' => $cat_fodder->id, 'unit_id' => $unit_bag->id, 'cost' => 450, 'price' => 550],
            ['name' => 'سيلاج ذرة فاخر', 'code' => 'F005', 'category_id' => $cat_fodder->id, 'unit_id' => $unit_ton->id, 'cost' => 3500, 'price' => 4500],

            // Fertilizers
            ['name' => 'نترات نشادر 33.5%', 'code' => 'E001', 'category_id' => $cat_fert->id, 'unit_id' => $unit_bag->id, 'cost' => 700, 'price' => 850],
            ['name' => 'يوريا 46.5% محبب', 'code' => 'E002', 'category_id' => $cat_fert->id, 'unit_id' => $unit_bag->id, 'cost' => 750, 'price' => 900],
            ['name' => 'سوبر فوسفات ناعم', 'code' => 'E003', 'category_id' => $cat_fert->id, 'unit_id' => $unit_bag->id, 'cost' => 400, 'price' => 500],
            ['name' => 'سلفات بوتاسيوم بودره', 'code' => 'E004', 'category_id' => $cat_fert->id, 'unit_id' => $unit_bag->id, 'cost' => 1400, 'price' => 1700],
            ['name' => 'حامض فسفوريك 80%', 'code' => 'E005', 'category_id' => $cat_fert->id, 'unit_id' => $unit_kg->id, 'cost' => 90, 'price' => 110],

            // Chemicals/Mubidat
            ['name' => 'مبيد حشري لانت (ميثوميل)', 'code' => 'C001', 'category_id' => $cat_chem->id, 'unit_id' => $unit_kg->id, 'cost' => 600, 'price' => 750],
            ['name' => 'مبيد فطري كوبروكسات', 'code' => 'C002', 'category_id' => $cat_chem->id, 'unit_id' => $unit_kg->id, 'cost' => 450, 'price' => 550],
            ['name' => 'منظم نمو جبريليك', 'code' => 'C003', 'category_id' => $cat_chem->id, 'unit_id' => $unit_kg->id, 'cost' => 120, 'price' => 180],
            ['name' => 'مبيد أعشاب عام (لانسر)', 'code' => 'C004', 'category_id' => $cat_chem->id, 'unit_id' => $unit_kg->id, 'cost' => 350, 'price' => 450],
            ['name' => 'مبيد عناكب (أبامكتين)', 'code' => 'C005', 'category_id' => $cat_chem->id, 'unit_id' => $unit_kg->id, 'cost' => 280, 'price' => 380],
        ];

        foreach ($products as $prod) {
            $product = Product::updateOrCreate(
                ['code' => $prod['code']],
                array_merge($prod, [
                    'Type_barcode' => 'CODE128',
                    'unit_sale_id' => $prod['unit_id'],
                    'unit_purchase_id' => $prod['unit_id'],
                    'tax_method' => '1',
                    'is_active' => true,
                    'stock_alert' => 10,
                ])
            );

            // Seed stock
            product_warehouse::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['qte' => rand(50, 500)]
            );
        }
    }
}
