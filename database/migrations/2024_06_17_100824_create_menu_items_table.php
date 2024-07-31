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
        // позиция меню
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            // блюдо
            $table->foreignId('dish_id');
            $table->foreign('dish_id')->references('id')->on('dishes');
            // цена
            $table->integer('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
