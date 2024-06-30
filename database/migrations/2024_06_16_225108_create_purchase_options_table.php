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
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // единиуа измерения
            $table->foreignId('unit_id');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            // наименование
            $table->text('name');
            // масса нетто
            $table->integer('net_weight');
            // задекларированная цена
            $table->decimal('declared_price');
            // поставщик
            $table->foreignId('distributor_id');
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade');
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
