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
            // единица измерения
            $table->foreignId('unit_id')->default(1);
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set default');
            // наименование
            $table->text('name')->unique();
            // масса нетто
            $table->integer('net_weight');
            // цена
            $table->decimal('price');
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
