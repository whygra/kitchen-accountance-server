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
        // связь M-N между компонентом и блюдом
        Schema::create('dish_components', function (Blueprint $table) {
            $table->id();
            // блюдо
            $table->foreignId('dish_id');
            $table->foreign('dish_id')->references('id')->on('dishes')->onDelete('cascade');
            // компонент
            $table->foreignId('component_id');
            $table->foreign('component_id')->references('id')->on('components')->onDelete('cascade');
            // процент отхода - потери компонента в весе при обработке
            $table->decimal('waste_percentage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dish_components');
    }
};
