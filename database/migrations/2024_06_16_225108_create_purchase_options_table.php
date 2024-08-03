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
        Schema::create('purchase_options', function (Blueprint $table) {
            $table->id();
            // продукт
            $table->foreignId('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            // единиуа измерения
            $table->foreignId('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            // наименование
            $table->text('name');
            // масса нетто
            $table->integer('net_weight');
            // цена
            
            $table->decimal('price');
            // поставщик
            $table->foreignId('distributor_id');
            $table->foreign('distributor_id')->references('id')->on('distributors');
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
