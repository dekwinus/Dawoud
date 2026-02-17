<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('payment_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_sales', 'status')) {
                $table->string('status', 32)->default('pending')->after('notes');
            }
            if (!Schema::hasColumn('payment_sales', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payment_sales', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('payment_sales', function (Blueprint $table) {
            if (Schema::hasColumn('payment_sales', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('payment_sales', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('payment_sales', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
