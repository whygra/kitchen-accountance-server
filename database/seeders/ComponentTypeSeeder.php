<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComponentTypeSeeder extends Seeder
{
    static String $tableName = 'component_types';
    /**
     * Run the database seeds.
     */
    public static function run(): void
    {
        DB::table(ComponentTypeSeeder::$tableName)->insert([
            'name' => 'ПФ',
        ]);
        DB::table(ComponentTypeSeeder::$tableName)->insert([
            'name' => 'ГП',
        ]);
    }
}
