<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this line for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('payment_methods')->where('id', 1)->update(['name' => 'Inistapay']);
        DB::table('payment_methods')->where('id', 3)->update(['name' => 'E-Wallet']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('payment_methods')->where('id', 1)->update(['name' => 'Credit Card']);
        DB::table('payment_methods')->where('id', 3)->update(['name' => 'Check']);
    }
};
