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

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->foreignId('subscription_plan_id');
            $table->foreign('subscription_plan_id')
                ->references('id')
                ->on('subscription_plans');
            
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');

        Schema::dropIfExists('subscription_plans');

    }
};
