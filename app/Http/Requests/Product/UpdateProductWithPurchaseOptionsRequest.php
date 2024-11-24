<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Http\Rules\ProductRules;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use App\Http\Rules\ProjectRules;


class UpdateProductWithPurchaseOptionsRequest extends ChecksPermissionsRequest
{
    
    public function __construct() {
        
        parent::__construct([PermissionNames::CRUD_PRODUCTS->value]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return  array_merge(
            ProjectRules::projectRules(),
            ProductRules::getUpdateProductRules($this->id, $this->project_id),
            ProductRules::productPurchaseOption($this->project_id),
            ProductRules::productGroupRules($this->project_id),
            ProductRules::productCategoryRules($this->project_id),
        );
    }
}
