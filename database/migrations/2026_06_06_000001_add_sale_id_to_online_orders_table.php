<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('online_orders', 'sale_id')) {
            Schema::table('online_orders', function (Blueprint $table) {
                $table->integer('sale_id')->nullable()->after('status');
                $table->foreign('sale_id')
                    ->references('id')
                    ->on('sales')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('online_orders', 'sale_id')) {
            Schema::table('online_orders', function (Blueprint $table) {
                $table->dropForeign(['sale_id']);
                $table->dropColumn('sale_id');
            });
        }
    }
};
