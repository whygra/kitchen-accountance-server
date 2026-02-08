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
            $table->string('name');
            // описание
            $table->string('description', 1000)->nullable();
            // тип компонента
            $table->foreignId('type_id');
            $table->foreign('type_id')->references('id')->on('ingredient_types');
            // вес 1 шт в граммах
            $table->decimal('item_weight')->default(1);
            // масса брутто
            $table->decimal('total_gross_weight')->default(0);
            // масса нетто
            $table->decimal('total_net_weight')->default(0);
            // признак - штучный ингредиент
            $table->boolean('is_item_measured')->default(0);
            // категория
            $table->foreignId('category_id')->nullable();
            $table->foreign('category_id')
                ->references('id')
                ->on('ingredient_categories')
                ->onDelete('set null');
            // группа
            $table->foreignId('group_id')->nullable();
            $table->foreign('group_id')
                ->references('id')
                ->on('ingredient_groups')
                ->onDelete('set null');
            // проект
            $table->foreignId('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');
            // пользователь, внесший последние изменения
            $table->foreignId('updated_by_user_id')->nullable();
            $table->foreign('updated_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->timestamps();

            $table->unique(['name', 'project_id']);
        });

        // связь M-N ингредиент-ингредиент
        Schema::create('ingredients_ingredients', function (Blueprint $table) {
            $table->id();
            // компонент
            $table->foreignId('includer_id');
            $table->foreign('includer_id')->references('id')->on('ingredients')->onDelete('cascade');
            // продукт
            $table->foreignId('included_id');
            $table->foreign('included_id')->references('id')->on('ingredients')->onDelete('cascade');
            // масса брутто
            $table->decimal('ingredient_amount');
            // нетто
            $table->decimal('net_weight');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients_ingredients');
        Schema::dropIfExists('ingredients');
    }
};
