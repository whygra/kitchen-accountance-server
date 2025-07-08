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
        Schema::create('purchase_acts', function (Blueprint $table) {
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
            // поставщик
            $table->foreignId('distributor_id');
            $table->foreign('distributor_id')
                ->references('id')
                ->on('distributors')
                ->onDelete('cascade');
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('purchase_acts_items', function (Blueprint $table) {
            $table->id();
            // акт закупки
            $table->foreignId('purchase_act_id');
            $table->foreign('purchase_act_id')->references('id')->on('purchase_acts')->onDelete('cascade');
            // продукт
            $table->foreignId('item_id');
            $table->foreign('item_id')->references('id')->on('purchase_options')->onDelete('cascade');
            // количество
            $table->decimal('amount');
            // нетто 1 ед
            $table->decimal('net_weight');
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
        Schema::dropIfExists('purchase_acts_items');
        Schema::dropIfExists('purchase_acts');
    }
};
