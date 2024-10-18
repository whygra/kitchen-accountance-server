<?php

namespace App\Http\Requests\Ingredient;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreIngredientWithProductsRequest extends FormRequest
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
            'name'=>'required|string|max:60|unique:ingredients,name',

            'type.id'=>'required|exists:ingredient_types,id',

            'category.id'=>'nullable',
            'category.name'=>[
                'nullable',
                'exclude_unless:category.id,0',
                'string',
                'max:60',
                'unique:ingredient_categories,name',
            ],

            'products'=>'nullable|array',
            'products.*.id'=>'required',
            'products.*.raw_content_percentage'=>'required|numeric|min:0|max:100',
            'products.*.waste_percentage'=>'required|numeric|min:0|max:100',
            'products.*.name'=>[
                'exclude_unless:products.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                'unique:products,name',
            ]
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Ошибки валидации: '.$validator->errors()->first(),
            'errors'      => $validator->errors()
        ], 400));
    }
}
