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
        Schema::create('dish_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
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
            $table->timestamps();
        });
        

        Schema::create('dishes_tags', function (Blueprint $table) {
            // ингредиент
            $table->foreignId('dish_id');
            $table->foreign('dish_id')->references('id')->on('dishes')->onDelete('cascade');
            // тег
            $table->foreignId('tag_id');
            $table->foreign('tag_id')->references('id')->on('dish_tags')->onDelete('cascade');

            $table->primary(['dish_id', 'tag_id']);
        });   
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishes_tags');
        Schema::dropIfExists('dish_tags');
    }
};
