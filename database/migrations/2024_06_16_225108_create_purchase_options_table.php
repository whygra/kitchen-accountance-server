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
        // позиция закупки
        Schema::create('purchase_options', function (Blueprint $table) {
            $table->id();
            // единица измерения
            $table->foreignId('unit_id')->nullable();
            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('set null');
            // код
            $table->integer('code')->nullable();
            // наименование
            $table->string('name');
            // масса нетто
            $table->integer('net_weight')->default(1000);
            // цена
            $table->decimal('price');
            // поставщик
            $table->foreignId('distributor_id');
            $table->foreign('distributor_id')
                ->references('id')
                ->on('distributors')
                ->onDelete('cascade');
            // пользователь, внесший последние изменения
            $table->foreignId('updated_by_user_id')->nullable();
            $table->foreign('updated_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->unique(['name','distributor_id']);
            $table->unique(['code','distributor_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_options');
    }
};
