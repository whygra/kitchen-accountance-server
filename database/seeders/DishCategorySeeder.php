<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DishCategorySeeder extends Seeder
{
    static String $tableName = 'dish_categories';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(DishCategorySeeder::$tableName)->select()->delete();
        DB::table(DishCategorySeeder::$tableName)->insert([
            'name' => 'без категории',
        ]);
    }
}
