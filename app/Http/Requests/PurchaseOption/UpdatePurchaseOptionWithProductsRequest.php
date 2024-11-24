<?php

namespace App\Http\Requests\PurchaseOption;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Http\Rules\PurchaseOptionRules;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Rules\ProjectRules;

use Spatie\Permission\Contracts\Permission;

class UpdatePurchaseOptionWithProductsRequest extends ChecksPermissionsRequest
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
            PurchaseOptionRules::getUpdatePurchaseOptionRules($this['distributor']['id'], $this->id),
            PurchaseOptionRules::purchaseOptionProductsRules($this->project_id),
            PurchaseOptionRules::purchaseOptionUnitRules($this->project_id),
        );
    }
}
