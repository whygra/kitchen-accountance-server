<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // связь M-N между заказом и позицией меню
        Schema::create('menu_items_orders', function (Blueprint $table) {
            $table->id();
            // заказ
            $table->foreignId('order_id');
            $table->foreign('order_id')->references('id')->on('orders');
            // позиция меню
            $table->foreignId('menu_item_id');
            $table->foreign('menu_item_id')->references('id')->on('menu_items');
            // количество заказанных единиц данной позиции меню
            $table->integer('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items_orders');
    }
};
