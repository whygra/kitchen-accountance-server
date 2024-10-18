<?php

namespace App\Http\Requests\Dish;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreDishWithIngredientsRequest extends FormRequest
{
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = User::find(Auth::user()->id);
        return empty($user) ? false : $user->hasAnyPermission([
            Permissions::CRUD_DISHES->value,
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
            'category.id'=>'required',
            'category.name'=>'nullable|exclude_unless:category.id,0|nullable|string|max:60|unique:dish_categories,name',

            'name'=>[
                'required',
                'string',
                'max:60',
                'unique:dishes,name',
            ],

            'ingredients'=>'nullable|array',
            'ingredients.*.id'=>'required',
            'ingredients.*.ingredient_amount'=>'required|numeric|min:1',
            'ingredients.*.waste_percentage'=>'required|numeric|min:0|max:99',
            'ingredients.*.name'=>[
                'exclude_unless:ingredients.*.id,0',
                'string',
                'max:60',
                'unique:ingredients,name',
                'distinct:ignore_case',
            ],
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => "Ошибки валидации:\n".$validator->errors()->first().' ...',
            'errors'    => $validator->errors()
        ], 400));
    }
}
