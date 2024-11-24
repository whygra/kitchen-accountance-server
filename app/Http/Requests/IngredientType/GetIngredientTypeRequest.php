<?php

namespace App\Http\Requests\IngredientType;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use App\Http\Rules\ProjectRules;


class GetIngredientTypeRequest extends ChecksPermissionsRequest
{
    
    public function __construct() {
        
        parent::__construct([
            PermissionNames::CRUD_INGREDIENTS->value,
            PermissionNames::READ_INGREDIENTS->value,
        ]);

    }

    public function rules(): array {
        return ProjectRules::projectRules();
    } 

}