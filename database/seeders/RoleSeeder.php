<?php

namespace Database\Seeders;

use App\Models\User\Permissions;
use App\Models\User\Roles;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public static function run(): void
    {
        Role::select()->delete();
        Role::create(['name' => Roles::VIEWER->value])
            ->syncPermissions([
                Permissions::READ_DISHES,
                Permissions::READ_DISTRIBUTORS,
                Permissions::READ_INGREDIENTS,
                Permissions::READ_PRODUCTS,
                Permissions::READ_USERS,
            ]);

        Role::create(['name' => Roles::CHEF->value])
            ->syncPermissions([
                Permissions::CRUD_DISHES,
                Permissions::CRUD_DISTRIBUTORS,
                Permissions::CRUD_INGREDIENTS,
                Permissions::CRUD_PRODUCTS,
                Permissions::READ_USERS,
            ]);

        Role::create(['name' => Roles::USER_MANAGER->value])
            ->syncPermissions([
                Permissions::CRUD_USERS,
            ]);

        Role::create(['name' => Roles::ADMIN->value])
            ->syncPermissions([
                Permissions::CRUD_USERS,
                Permissions::CRUD_DISHES,
                Permissions::CRUD_DISTRIBUTORS,
                Permissions::CRUD_INGREDIENTS,
                Permissions::CRUD_PRODUCTS,
            ]);
    }
}
