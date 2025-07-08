<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */public function up(): void
    {
        Schema::create('write_off_acts', function (Blueprint $table) {
            $table->id();
            // пользователь, внесший последние изменения
            $table->foreignId('updated_by_user_id')->nullable();
            $table->foreign('updated_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            // проект
            $table->foreignId('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('write_off_acts_products', function (Blueprint $table) {
            $table->id();
            // акт инвентаризации
            $table->foreignId('write_off_act_id');
            $table->foreign('write_off_act_id')->references('id')->on('write_off_acts')->onDelete('cascade');
            // продукт
            $table->foreignId('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // количество
            $table->decimal('amount');
            // масса нетто 1 ед
            $table->decimal('net_weight');
            $table->timestamps();
        });        

        Schema::create('write_off_acts_ingredients', function (Blueprint $table) {
            $table->id();
            // акт инвентаризации
            $table->foreignId('write_off_act_id');
            $table->foreign('write_off_act_id')->references('id')->on('write_off_acts')->onDelete('cascade');
            // продукт
            $table->foreignId('ingredient_id');
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
            // количество
            $table->decimal('amount');
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('write_off_acts_products');
        Schema::dropIfExists('write_off_acts_ingredients');
        Schema::dropIfExists('write_off_acts');
    }
};
