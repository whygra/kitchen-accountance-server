<?php

namespace App\Http\Requests\Dish;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Http\Requests;
use App\Http\Rules\DishRules;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Rules\ProjectRules;


class StoreDishWithIngredientsRequest extends ChecksPermissionsRequest
{
    
    public function __construct() {
        
        parent::__construct([PermissionNames::CRUD_DISHES->value]);
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
            DishRules::storeDishRules($this->project_id),
            DishRules::getDishCategoryRules($this->project_id),
            DishRules::dishGroupRules($this->project_id),
            DishRules::dishIngredientsRules($this->project_id)
        );
    }
}
