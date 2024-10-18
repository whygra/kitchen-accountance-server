<?php

namespace App\Http\Requests\Dish;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateDishWithIngredientsRequest extends FormRequest
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

    public function rules(): array
    {
        return [
            'id'=>'required|exists:ingredients,id',

            'name'=>[
                'required',
                'string',
                'max:60',
                'unique:dishes,name,'.$this['id']
            ],

            'category.id'=>'required',
            'category.name'=>'nullable|exclude_unless:category.id,0|nullable|string|max:60|unique:dish_categories,name',

            'ingredients'=>'nullable|array',
            'ingredients.*.id'=>'required',
            'ingredients.*.ingredient_amount'=>'required|numeric|min:1',
            'ingredients.*.waste_percentage'=>'required|numeric|min:0|max:99',
            'ingredients.*.name'=> [
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
            'message'   => $validator->errors()->first().'. (и ещё '.count($validator->errors())." ошибок)",
            'errors'    => $validator->errors()
        ], 400));
    }
}
