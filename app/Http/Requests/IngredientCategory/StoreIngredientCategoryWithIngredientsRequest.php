<?php

namespace App\Http\Requests\IngredientCategory;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreIngredientCategoryWithIngredientsRequest extends FormRequest
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
        ], 401));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'name'=>'required|string',
            'ingredients'=>'array|nullable',
            'ingredients.*.id'=>'required',
            'ingredients.*.name'=>'required|string',
            'ingredients.*.type_id'=>'required|exists:ingredient_types,id',
            'ingredients.*.category_id'=>'required|exists:ingredient_categories,id',
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Ошибка валидации',
            'errors'      => $validator->errors()
        ], 400));
    }
}
