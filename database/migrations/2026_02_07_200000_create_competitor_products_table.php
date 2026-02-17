<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetitorProductsTable extends Migration
{
    public function up()
    {
        Schema::create('competitor_products', function (Blueprint $table) {
            $table->id();
            $table->string('competitor_name');
            $table->string('product_name');
            $table->string('code')->nullable();
            $table->double('price', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('image')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category_id');
            $table->index('brand_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('competitor_products');
    }
}
