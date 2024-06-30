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
        // пункт закупки
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            // позиция закупки
            $table->foreignId('purchase_item_id');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_items')->onDelete('cascade');
            // закупка
            $table->foreignId('purchase_id');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            // количество единиц
            $table->integer('amount');
            // цена
            $table->decimal('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
