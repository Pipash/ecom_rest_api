<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->double('price', 10, 2);
            $table->text('description')->nullable();
            $table->char('size', 5)->nullable();
            $table->char('color', 10)->nullable();
            $table->float('discount_price')->nullable();
            $table->integer('discount_percent')->nullable();
            $table->bigInteger('parent_product_id')->nullable();
            $table->boolean('is_bundle')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
