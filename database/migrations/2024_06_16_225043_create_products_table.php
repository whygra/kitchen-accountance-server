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
        // продукт
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // наименование
            $table->string('name');
            // категория
            $table->foreignId('category_id')->nullable();
            $table->foreign('category_id')
                ->references('id')
                ->on('product_categories')
                ->onDelete('set null');
            // группа
            $table->foreignId('group_id')->nullable();
            $table->foreign('group_id')
                ->references('id')
                ->on('product_groups')
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
        Schema::dropIfExists('products');
    }
};
