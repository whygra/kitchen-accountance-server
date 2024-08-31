<?php

namespace Database\Seeders;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public static function run(): void
    {
        Permission::select()->delete();
        Permission::create(['name' => Permissions::CRUD_DISHES->value]);
        Permission::create(['name' => Permissions::CRUD_DISTRIBUTORS->value]);
        Permission::create(['name' => Permissions::CRUD_PRODUCTS->value]);
        Permission::create(['name' => Permissions::CRUD_INGREDIENTS->value]);
        Permission::create(['name' => Permissions::CRUD_USERS->value]);
        Permission::create(['name' => Permissions::READ_DISHES->value]);
        Permission::create(['name' => Permissions::READ_DISTRIBUTORS->value]);
        Permission::create(['name' => Permissions::READ_PRODUCTS->value]);
        Permission::create(['name' => Permissions::READ_INGREDIENTS->value]);
        Permission::create(['name' => Permissions::READ_USERS->value]);
    }
}
