<?php

namespace Database\Seeders;

use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public static function run(): void
    {
        User::select()->delete();
        User::create([
            'name' => env('SUPERUSER_NAME'),
            'email' => env('SUPERUSER_EMAIL'),
            'password' => Hash::make(env('SUPERUSER_PASSWORD')),
        ]);
    }
}
