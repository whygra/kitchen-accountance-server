<?php

namespace Database\Seeders;

use App\Models\User\Permission;
use App\Models\User\PermissionNames;
use App\Models\User\Role;
use App\Models\User\RoleNames;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public static function run(): void
    {

        Role::select()->delete();
        Role::create(['name' => RoleNames::VIEWER->value])
            ->permissions()->sync([
                Permission::where('name', PermissionNames::READ_DISHES->value)->first()->id,
                Permission::where('name', PermissionNames::READ_DISTRIBUTORS->value)->first()->id,
                Permission::where('name', PermissionNames::READ_INGREDIENTS->value)->first()->id,
                Permission::where('name', PermissionNames::READ_PRODUCTS->value)->first()->id,
                Permission::where('name', PermissionNames::READ_USERS->value)->first()->id,
            ]);

        Role::create(['name' => RoleNames::CHEF->value])
            ->permissions()->sync([
                Permission::where('name', PermissionNames::CRUD_DISHES->value)->first()->id,
                Permission::where('name', PermissionNames::CRUD_DISTRIBUTORS->value)->first()->id,
                Permission::where('name', PermissionNames::CRUD_INGREDIENTS->value)->first()->id,
                Permission::where('name', PermissionNames::CRUD_PRODUCTS->value)->first()->id,
                Permission::where('name', PermissionNames::READ_USERS->value)->first()->id,
            ]);

        Role::create(['name' => RoleNames::USER_MANAGER->value])
            ->permissions()->sync([
                Permission::where('name', PermissionNames::CRUD_USERS->value)->first()->id,
            ]);

        Role::create(['name' => RoleNames::PROJECT_MANAGER->value])
            ->permissions()->sync([
                Permission::where('name', PermissionNames::CRUD_USERS->value)->first()->id,
                Permission::where('name', PermissionNames::EDIT_PROJECT->value)->first()->id,
            ]);

        Role::create(['name' => RoleNames::ADMIN->value])
            ->permissions()->sync([
                Permission::where('name', PermissionNames::CRUD_DISHES->value)->first()->id,
                Permission::where('name', PermissionNames::CRUD_DISTRIBUTORS->value)->first()->id,
                Permission::where('name', PermissionNames::CRUD_INGREDIENTS->value)->first()->id,
                Permission::where('name', PermissionNames::CRUD_PRODUCTS->value)->first()->id,
                Permission::where('name', PermissionNames::CRUD_USERS->value)->first()->id,
                Permission::where('name', PermissionNames::EDIT_PROJECT->value)->first()->id,
            ]);
    }
}
