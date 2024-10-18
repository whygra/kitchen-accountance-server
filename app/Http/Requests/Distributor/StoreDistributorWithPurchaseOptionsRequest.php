<?php

namespace App\Http\Requests\Distributor;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDistributorWithPurchaseOptionsRequest extends FormRequest
{
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = User::find(Auth::user()->id);
        return empty($user) ? false : $user->hasAnyPermission([
            Permissions::CRUD_DISTRIBUTORS->value,
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
            'name'=>'required|string|max:60|unique:distributors,name',

            'purchase_options'=>'nullable|array',
            // при добавлении данных поставщика позиции закупки только создаются
            'purchase_options.*.name'=>[
                'required',
                'string',
                'max:60',
                'unique:purchase_options,name',
                'distinct:ignore_case'
            ],
            'purchase_options.*.unit.id'=>'required',
            'purchase_options.*.unit.long'=>[
                'exclude_unless:purchase_options.unit.id,0',
                'string',
                'max:120',
                'unique:units,long',
                'distinct:ignore_case'
            ],
            'purchase_options.*.unit.short'=>[
                'exclude_unless:purchase_options.unit.id,0',
                'string',
                'max:6',
                'unique:units,short',
                'distinct:ignore_case'
            ],

            'purchase_options.*.products'=>'required|array',
            'purchase_options.*.products.*.id'=>'required|distinct',
            'purchase_options.*.products.*.name'=>[
                'exclude_unless:purchase_options.*.products.*.id,0',
                'string',
                'max:60',
                'unique:products,name',
                'distinct:ignore_case',
            ]
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Ошибки валидации',
            'errors'      => $validator->errors()
        ], 400));
    }
}
