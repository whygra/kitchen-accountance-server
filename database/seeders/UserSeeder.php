<?php

namespace Database\Seeders;

use App\Models\User\SubscriptionPlan;
use App\Models\User\SubscriptionPlanNames;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public static function run(): void
    {
        User::select()->delete();
        User::create(attributes: [
            'name' => env('SUPERUSER_NAME'),
            'email' => env('SUPERUSER_EMAIL'),
            'password' => Hash::make(env('SUPERUSER_PASSWORD')),
            'subscription_plan_id' => SubscriptionPlan::where('name', SubscriptionPlanNames::PREMIUM)->first()->id,
        ]);
        User::create(attributes: [
            'name' => User::GUEST_NAME,
            'email' => User::GUEST_NAME,
            'password' => User::GUEST_NAME,
            'subscription_plan_id' => SubscriptionPlan::where('name', SubscriptionPlanNames::NONE)->first()->id,
        ]);
    }
}
