<?php

namespace App\Http\Requests\Distributor;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Http\Rules\DistributorRules;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Rules\ProjectRules;


class StoreDistributorWithPurchaseOptionsRequest extends ChecksPermissionsRequest
{

    public function __construct() {
        
        parent::__construct([PermissionNames::CRUD_DISTRIBUTORS->value]);
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
            DistributorRules::storeDistributorRules($this->project_id), 
            DistributorRules::storeDistributorPurchaseOptionsRules($this->project_id), 
        );
    }
}
