<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert some stuff
        DB::table('currencies')->updateOrInsert(
            ['id' => 1],
            [
                'code' => 'EGP',
                'name' => 'Egyptian Pound',
                'symbol' => 'EGP',
            ]
        );
    }
}
