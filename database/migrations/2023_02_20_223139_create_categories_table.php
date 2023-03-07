<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->bigInteger('parent_id')->nullable();
            $table->boolean('is_popular')->default(0);
            $table->mediumText('desc')->nullable();
            $table->string('icon')->nullable();
            $table->text('icon_svg')->nullable();
            $table->string('img')->nullable();
            $table->integer('position')->default(1000);
            $table->string('slug')->unique();
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
        Schema::dropIfExists('categories');
    }
}
