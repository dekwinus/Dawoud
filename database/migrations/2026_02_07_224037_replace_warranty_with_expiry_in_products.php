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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['warranty_period', 'warranty_unit', 'warranty_terms']);
            $table->date('production_date')->nullable()->after('is_active');
            $table->date('expiry_date')->nullable()->after('production_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('warranty_period')->nullable();
            $table->string('warranty_unit')->nullable();
            $table->text('warranty_terms')->nullable();
            $table->dropColumn(['production_date', 'expiry_date']);
        });
    }
};
