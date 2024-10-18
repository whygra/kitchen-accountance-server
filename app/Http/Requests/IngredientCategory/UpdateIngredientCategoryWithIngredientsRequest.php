<?php

namespace App\Http\Requests\IngredientCategory;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateIngredientCategoryWithIngredientsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = User::find(Auth::user()->id);
        return empty($user) ? false : $user->hasAnyPermission([
            Permissions::CRUD_INGREDIENTS->value,
        ]);
    }

     public function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Нет прав доступа: '.$this::class,
        ], 403));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'id'=>'required|exists:ingredient_categories,id',
            'name'=>'required|string|max:60|unique:ingredient_categories,name,'.$this['id'],

            'ingredients'=>'nullable|array',
            'ingredients.*.id'=>'required',
            'ingredients.*.name'=>[
                'exclude_unless:ingredients.*.id,0',
                'string',
                'max:60',
                'unique:dishes,name',
                'distinct:ignore_case',
            ],
            'ingredients.*.type.id'=>'required|exists:ingredient_types,id',

        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Ошибки валидации',
            'errors'    => $validator->errors()
        ], 400));
    }
}
