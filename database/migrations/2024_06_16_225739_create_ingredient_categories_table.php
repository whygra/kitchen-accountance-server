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
        Schema::create('ingredient_categories', function (Blueprint $table) {
            $table->id();
            // название
            $table->string('name');
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

            $table->unique(['name','project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_categories');
    }
};
