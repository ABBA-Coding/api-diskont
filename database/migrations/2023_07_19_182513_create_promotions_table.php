<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            // files
            $table->string('banner')->nullable();
            $table->string('short_name_icon')->nullable();
            $table->string('sticker')->nullable();

            $table->text('short_name')->nullable();
            $table->text('short_name_icon_svg')->nullable();
            $table->string('short_name_first_color')->nullable();
            $table->string('short_name_last_color')->nullable();

            $table->text('name');
            $table->text('desc')->nullable();
            $table->date('start_date');
            $table->date('end_date');

            $table->text('sticker_svg')->nullable();
            $table->text('product_card_text')->nullable();
            $table->string('product_card_text_color')->default('#ffffff');
            $table->string('product_card_back_color')->default('#000000');

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
        Schema::dropIfExists('promotions');
    }
}
