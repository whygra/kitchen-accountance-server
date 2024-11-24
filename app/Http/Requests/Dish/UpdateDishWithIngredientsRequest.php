<?php

namespace App\Http\Requests\Dish;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Http\Rules\DishRules;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use App\Http\Rules\ProjectRules;


class UpdateDishWithIngredientsRequest extends ChecksPermissionsRequest
{
    
    public function __construct() {
        
        parent::__construct([PermissionNames::CRUD_DISHES->value]);
    }

    public function rules(): array
    {
        return array_merge(
            ProjectRules::projectRules(),
            DishRules::getUpdateDishRules($this->id, $this->project_id),
            DishRules::getDishCategoryRules($this->project_id),
            DishRules::dishGroupRules($this->project_id),
            DishRules::dishIngredientsRules($this->project_id)
        );
    }
}
