<?php

namespace Database\Seeders;

use App\Models\User\SubscriptionPlanNames;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    static String $tableName = 'subscription_plans';
    /**
     * Run the database seeds.
     */
    public static function run(): void
    {
        DB::table(SubscriptionPlanSeeder::$tableName)->select()->delete();
        DB::table(SubscriptionPlanSeeder::$tableName)->insert([
            'name' => SubscriptionPlanNames::NONE,
            
            'max_num_projects' => 1,

            'max_num_distributors' => 2,
            'max_num_purchase_options' => 100,
            'max_num_units' => 5,

            'max_num_products' => 200,
            'max_num_ingredients' => 100,
            'max_num_dishes' => 20,
            'max_num_tags' => 5,
        ]);
        DB::table(SubscriptionPlanSeeder::$tableName)->insert([
            'name' => SubscriptionPlanNames::PREMIUM,
            
            'max_num_projects' => 5,

            'max_num_distributors' => 10,
            'max_num_purchase_options' => 4000,
            'max_num_units' => 15,

            'max_num_products' => 20000,
            'max_num_ingredients' => 10000,
            'max_num_dishes' => 2000,
            'max_num_tags' => 50,
        ]);
    }
}
