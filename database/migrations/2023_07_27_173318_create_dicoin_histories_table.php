<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDicoinHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dicoin_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->enum('type', ['plus', 'minus']);
            $table->bigInteger('order_id')->nullable();
            $table->integer('quantity');
            $table->dateTime('expired_at')->nullable();
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
        Schema::dropIfExists('dicoin_histories');
    }
}
