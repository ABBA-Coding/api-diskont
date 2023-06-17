<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInProductBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_badges', function (Blueprint $table) {
            $table->string('background_color')->nullable()->default('#000000');
            $table->string('text_color')->nullable()->default('#ffffff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_badges', function (Blueprint $table) {
            $table->dropColumn(['background_color', 'text_color']);
        });
    }
}
