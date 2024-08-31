<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientCategorySeeder extends Seeder
{
    static String $tableName = 'ingredient_categories';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(IngredientCategorySeeder::$tableName)->select()->delete();
        DB::table(IngredientCategorySeeder::$tableName)->insert([
            'name' => 'без категории',
        ]);
    }
}
