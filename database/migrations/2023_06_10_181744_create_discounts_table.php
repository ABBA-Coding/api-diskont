<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('desc')->nullable();
            $table->integer('percent')->nullable();
            $table->bigInteger('amount')->nullable();
            $table->enum('type', ['product', 'brand']);
            $table->text('ids');
            $table->date('start');
            $table->date('end')->nullable()->comment('esli null vruchnuyu ostanovyat');
            $table->boolean('status')->default(1);
            $table->text('for_search')->nullable();
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
        Schema::dropIfExists('discounts');
    }
}
