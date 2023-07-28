<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDicoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dicoins', function (Blueprint $table) {
            $table->id();
            $table->integer('sum_to_dicoin')->default(100000);
            $table->integer('dicoin_to_sum')->default(10000);
            $table->integer('dicoin_to_reg')->default(10);
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
        Schema::dropIfExists('dicoins');
    }
}
