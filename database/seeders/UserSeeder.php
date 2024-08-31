<?php

namespace Database\Seeders;

use App\Models\User\Roles;
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
            'name' => 'superadmin',
            'email' => 'why.grabovsky@gmail.com',
            'password' => Hash::make(env('DB_PASSWORD')),
        ])->assignRole(Roles::ADMIN->value);
    }
}
