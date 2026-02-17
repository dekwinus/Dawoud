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
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'approval_status')) {
                $table->string('approval_status', 32)->default('approved')->after('payment_statut');
            }
            if (!Schema::hasColumn('sales', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('sales', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('sales', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('sales', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });
    }
};
