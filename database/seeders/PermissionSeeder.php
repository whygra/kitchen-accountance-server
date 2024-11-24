<?php

namespace Database\Seeders;

use App\Models\User\Permission;
use App\Models\User\PermissionNames;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public static function run(): void
    {
        Permission::select()->delete();
        
        Permission::create(['name'=>PermissionNames::EDIT_PROJECT->value]);

        Permission::create(['name'=>PermissionNames::CRUD_USERS->value]);
        Permission::create(['name'=>PermissionNames::CRUD_DISHES->value]);
        Permission::create(['name'=>PermissionNames::CRUD_DISTRIBUTORS->value]);
        Permission::create(['name'=>PermissionNames::CRUD_INGREDIENTS->value]);
        Permission::create(['name'=>PermissionNames::CRUD_PRODUCTS->value]);
        
        Permission::create(['name'=>PermissionNames::READ_DISHES->value]);
        Permission::create(['name'=>PermissionNames::READ_DISTRIBUTORS->value]);
        Permission::create(['name'=>PermissionNames::READ_INGREDIENTS->value]);
        Permission::create(['name'=>PermissionNames::READ_PRODUCTS->value]);
        Permission::create(['name'=>PermissionNames::READ_USERS->value]);
    }
}
