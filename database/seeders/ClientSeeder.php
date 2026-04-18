<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clients')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'walk-in-customer',
                'code' => 1,
                'email' => 'walk-in-customer@example.com',
                'country' => 'Egypt',
                'city' => 'Cairo',
                'phone' => '+201060909402',
                'adresse' => 'Cairo, Egypt',
                'tax_number' => null,
                'is_royalty_eligible' => 1,
                'points' => 0,
            ]
        );
    }
}
