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
        if (!Schema::hasTable('payment_status_logs')) {
            Schema::create('payment_status_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_sale_id');
                $table->string('previous_status')->nullable();
                $table->string('new_status');
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->timestamp('changed_at')->nullable();
                $table->timestamps();
                
                $table->index('payment_sale_id');
                $table->index('changed_by');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('payment_status_logs');
    }
};
