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
        // закупка
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            // дата
            $table->date('date');
            // признак - выполнена
            $table->boolean('isDelivered');
            // признак - оплачена
            $table->boolean('isPaid');
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
        Schema::dropIfExists('purchases');
    }
};
