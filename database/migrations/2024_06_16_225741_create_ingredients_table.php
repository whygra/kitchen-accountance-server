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
            $table->string('description');
            // тип компонента
            $table->foreignId('type_id');
            $table->foreign('type_id')->references('id')->on('ingredient_types');
            // вес 1 шт в граммах
            $table->decimal('item_weight')->default(1);
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
