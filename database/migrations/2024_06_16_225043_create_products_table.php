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
        // продукт
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // наименование
            $table->text('name');
            // категория
            $table->foreignId('category_id')->default('1');
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('set default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
