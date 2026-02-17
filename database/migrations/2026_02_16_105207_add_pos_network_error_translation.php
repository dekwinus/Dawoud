<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insertOrIgnore([
            'locale' => 'en',
            'key' => 'pos.NetworkError',
            'value' => 'Network error, please try again.',
        ]);
        
        // Optionally insert for other languages too if desired, using same english text as fallback
        // Or leave it to the user to translate later.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('translations')
            ->where('locale', 'en')
            ->where('key', 'pos.NetworkError')
            ->delete();
    }
};
