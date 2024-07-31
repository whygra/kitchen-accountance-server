<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            // скидка
            $table->decimal('discount');
            // позиция закупки
            $table->foreignId('purchase_option_id');
            $table->foreign('purchase_option_id')->references('id')->on('purchase_options');
            // закупка
            $table->foreignId('purchase_id');
            $table->foreign('purchase_id')->references('id')->on('purchases');
            // количество единиц
            $table->integer('amount');
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
