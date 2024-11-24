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
        // единица измерения
        Schema::create('users_projects', function (Blueprint $table) {
            $table->id();
            // пользователь
            $table->foreignId('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            // проект
            $table->foreignId('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');
            // роль
            $table->foreignId('role_id')->nullable();
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('set null');
               
            $table->timestamps();

            $table->unique(['user_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_projects');
    }
};
