<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    static String $tableName = 'product_categories';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(ProductCategorySeeder::$tableName)->insert([
            'name' => 'без категории',
        ]);
    }
}
