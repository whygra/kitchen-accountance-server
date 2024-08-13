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
        // позиция закупки
        Schema::create('products_purchase_options', function (Blueprint $table) {
            $table->id();
            // продукт
            $table->foreignId('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // единица измерения
            $table->foreignId('purchase_option_id');
            $table->foreign('purchase_option_id')->references('id')->on('purchase_options')->onDelete('cascade');
            // доля веса продукта в массе нетто товара
            $table->decimal('product_share');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_options');
    }
};
