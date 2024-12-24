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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            
            $table->integer('max_num_projects');

            $table->integer('max_num_distributors');
            $table->integer('max_num_purchase_options');
            $table->integer('max_num_units');

            $table->integer('max_num_products');
            $table->integer('max_num_product_categories');
            $table->integer('max_num_ingredients');
            $table->integer('max_num_ingredient_categories');
            $table->integer('max_num_dishes');
            $table->integer('max_num_dish_categories');
            $table->timestamps();
        });
    }
    
        
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
