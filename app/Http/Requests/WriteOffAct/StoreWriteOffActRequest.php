<?php

namespace App\Http\Requests\WriteOffAct;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Http\Rules\WriteOffActRules;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use App\Http\Rules\ProjectRules;


class StoreWriteOffActRequest extends ChecksPermissionsRequest
{
    
    public function __construct() {
        
        parent::__construct([PermissionNames::CRUD_STORAGE->value]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return array_merge(
            ProjectRules::projectRules(),
            WriteOffActRules::storeWriteOffActRules($this->project_id),
        );  
    }
}
