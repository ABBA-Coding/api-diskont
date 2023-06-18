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
            $table->bigInteger('info_id');
            $table->string('c_id')->unique()->nullable();
            $table->string('model')->nullable();
            $table->integer('price')->nullable();
            $table->boolean('is_popular')->default(0);
            $table->boolean('product_of_the_day')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_available')->default(1);
            $table->string('slug')->unique();
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
