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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // название
            $table->string('name');
            // логотип
            $table->string('logo_name')->nullable()->unique();
            // фон
            $table->string('backdrop_name')->nullable()->unique();
            // создатель
            $table->foreignId('creator_id')->nullable();
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            // пользователь, внесший последние изменения
            $table->foreignId('updated_by_user_id')->nullable();
            $table->foreign('updated_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->unique(['name', 'creator_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
