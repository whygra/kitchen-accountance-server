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
        Schema::create('sale_acts', function (Blueprint $table) {
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

        Schema::create('sale_acts_items', function (Blueprint $table) {
            $table->id();
            // акт продажи
            $table->foreignId('sale_act_id');
            $table->foreign('sale_act_id')->references('id')->on('sale_acts')->onDelete('cascade');
            // блюдо
            $table->foreignId('item_id');
            $table->foreign('item_id')->references('id')->on('dishes')->onDelete('cascade');
            // количество
            $table->decimal('amount');
            // цена
            $table->decimal('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_acts_items');
        Schema::dropIfExists('sale_acts');
    }
};
