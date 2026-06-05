<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert some stuff
        DB::table('users')->updateOrInsert(
            ['id' => 1],
            [
                'firstname' => 'Ahmed',
                'lastname' => 'Dawoud',
                'username' => 'Ahmed Dawoud',
                'email' => 'admin@dawoud.co',
                'password' => '$2y$10$I9SgCoCXaVvxlQCVBJIqIe/wpoqF.lgG2ljTM/HnzpBPCbtIcLiG6',
                'avatar' => 'no_avatar.png',
                'phone' => '+201060909402',
                'role_id' => 1,
                'statut' => 1,
                'is_all_warehouses' => 1,
                'record_view' => 1,
            ]
        );
    }
}
