<?php

namespace App\Http\Requests\InventoryAct;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Models\User\PermissionNames;
use App\Http\Rules\ProjectRules;


class GetInventoryActRequest extends ChecksPermissionsRequest
{
    
    public function __construct() {
        
        parent::__construct([
            PermissionNames::CRUD_STORAGE->value,
            PermissionNames::READ_STORAGE->value,
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
