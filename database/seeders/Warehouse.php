<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Warehouse extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert some stuff
        DB::table('warehouses')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Default Warehouse',
                'city' => 'Cairo',
                'mobile' => '+201060909402',
                'zip' => '11511',
                'email' => 'admim@dawoud.co',
                'country' => 'Egypt',
            ]
        );
    }
}
