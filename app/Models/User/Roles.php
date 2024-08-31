<?php

namespace App\Models\User;

enum Roles: string
{
    // case NAMEINAPP = 'name-in-database';
    case VIEWER = 'viewer';
    case CHEF = 'chef';
    case USER_MANAGER = 'user-manager';
    case ADMIN = 'admin';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            static::VIEWER => 'Viewers',
            static::CHEF => 'Head Chefs',
            static::USER_MANAGER => 'User Managers',
            static::ADMIN => 'Admins',
        };
    }
}