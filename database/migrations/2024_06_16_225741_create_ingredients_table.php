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
        // компонент
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            // название
            $table->text('name');
            // тип компонента
            $table->foreignId('type_id');
            $table->foreign('type_id')->references('id')->on('ingredient_types');
            // категория
            $table->foreignId('category_id')->default('1');
            $table->foreign('category_id')->references('id')->on('ingredient_categories')->onDelete('set default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
