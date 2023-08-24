<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bars', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('promotion_id')->nullable();
            $table->bigInteger('category_id')->nullable();
            $table->text('name');
            $table->string('icon')->nullable();
            $table->text('icon_svg')->nullable();
            $table->string('text_color')->default('#ffffff');
            $table->string('color1')->default('#000000');
            $table->string('color2')->default('#000000');

            $table->integer('position')->default(1000);

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
        Schema::dropIfExists('bars');
    }
}
