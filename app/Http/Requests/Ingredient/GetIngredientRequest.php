<?php

namespace App\Http\Requests\Ingredient;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Models\User\PermissionNames;
use App\Http\Rules\ProjectRules;


class GetIngredientRequest extends ChecksPermissionsRequest
{
    
    public function __construct() {
        
        parent::__construct([
            PermissionNames::CRUD_INGREDIENTS->value,
            PermissionNames::READ_INGREDIENTS->value,
        ]);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ProjectRules::projectRules();
    }
}
