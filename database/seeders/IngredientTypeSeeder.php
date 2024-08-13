<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientTypeSeeder extends Seeder
{
    static String $tableName = 'ingredient_types';
    /**
     * Run the database seeds.
     */
    public static function run(): void
    {
        DB::table(IngredientTypeSeeder::$tableName)->insert([
            'name' => 'ПФ',
        ]);
        DB::table(IngredientTypeSeeder::$tableName)->insert([
            'name' => 'ГП',
        ]);
    }
}
