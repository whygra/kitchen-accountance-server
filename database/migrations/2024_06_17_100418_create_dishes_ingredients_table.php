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
        // связь M-N между компонентом и блюдом
        Schema::create('dishes_ingredients', function (Blueprint $table) {
            $table->id();
            // блюдо
            $table->foreignId('dish_id');
            $table->foreign('dish_id')->references('id')->on('dishes')->onDelete('cascade');
            // компонент
            $table->foreignId('ingredient_id');
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
            // масса брутто/количество
            $table->decimal('ingredient_amount');
            // нетто
            $table->decimal('net_weight');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishes_ingredients');
    }
};
