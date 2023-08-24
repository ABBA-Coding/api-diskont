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
            $table->id();
            $table->bigInteger('info_id')->nullable();
            $table->string('c_id')->unique()->nullable();
            $table->string('model')->nullable();
            $table->integer('price')->nullable();
            $table->integer('installment_price_6')->nullable();
            $table->integer('installment_price_12')->nullable();
            $table->integer('installment_price_18')->nullable();
            $table->integer('installment_price_24')->nullable();
            $table->integer('installment_price_36')->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('is_popular')->default(0);
            $table->boolean('product_of_the_day')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_available')->default(1);
            $table->string('slug')->unique();

            $table->integer('dicoin')->nullable(); // skolko procentov ot obshey summi mojet platit dicoinami
            $table->text('name');
            $table->text('for_search')->nullable();
//            $table->text('name')->nullable();
//            $table->text('desc')->nullable();
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
