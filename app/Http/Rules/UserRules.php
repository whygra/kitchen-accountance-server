<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class UserRules {

    public static function assignUserRoleRules() {
        return [
            'id'=>'required|exists:users,id',
            'role.id'=>'required|exists:roles,id',
        ];
    }
}