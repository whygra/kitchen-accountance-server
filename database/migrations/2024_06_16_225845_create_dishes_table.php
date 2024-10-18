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
        // блюдо
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            // название
            $table->string('name')->unique();
            // путь к файлу изображения
            $table->string('image_path')->nullable();
            // категория
            $table->foreignId('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('dish_categories')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
