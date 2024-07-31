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
    {   // связь M-N продукта с компонентом
        Schema::create('ingredients_products', function (Blueprint $table) {
            $table->id();
            // компонент
            $table->foreignId('ingredient_id');
            $table->foreign('ingredient_id')->references('id')->on('ingredients');
            // продукт
            $table->foreignId('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            // доля продукта в общем весе необработанных продуктов компонента
            $table->decimal('raw_content_percentage');
            // процент отхода - потери продукта в весе при обработке
            $table->decimal('waste_percentage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients_products');
    }
};
