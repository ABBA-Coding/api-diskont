<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->enum('delivery_method', ['pickup', 'courier']);
            $table->text('name');
            $table->text('surname');
            $table->string('phone_number');
            $table->bigInteger('user_address_id')->nullable();
            $table->string('postcode')->nullable();
            $table->string('email')->nullable();
            $table->text('comments')->nullable();
            $table->enum('payment_method', ['cash', 'payme', 'uzum', 'click', 'payze']);
            /*
             * [{"product_id" => 1, "count" => 2, "price" => 150000, "price_with_discount" => 120000}, ...]
             */
            $table->text('products');
            $table->bigInteger('amount');
            $table->boolean('is_paid')->default(0);
            /*
             * pending - v ojidanii tovara(masalan tovar hali skladda yo'q, klient kutvotti)
             */
            $table->enum('status', ['new', 'canceled', 'accepted', 'done', 'returned', 'pending']);

            $table->integer('delivery_price')->default(0);
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
        Schema::dropIfExists('orders');
    }
}
