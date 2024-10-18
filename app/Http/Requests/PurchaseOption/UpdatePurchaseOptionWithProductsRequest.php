<?php

namespace App\Http\Requests\PurchaseOption;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Contracts\Permission;

class UpdatePurchaseOptionWithProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = User::find(Auth::user()->id);
        return empty($user) ? false : $user->hasAnyPermission(Permissions::CRUD_DISTRIBUTORS->value);
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
            'id'=>'required|exists:purchase_options,id',
            'name'=>[
                'required',
                'string',
                'max:120',
                Rule::unique('purchase_options', 'name')
                    ->where('distributor_id', $this['distributor']['id'])
                    ->ignore($this['id']),
            ],
            'unit.id'=>'required',
            'unit.long'=>[
                'exclude_unless:purchase_options.unit.id,0',
                'string',
                'max:60',
                'unique:units,long',
            ],
            'unit.short'=>[
                'exclude_unless:purchase_options.unit.id,0',
                'string',
                'max:6',
                'unique:units,short',
            ],

            'net_weight'=>'required|numeric|min:1',
            'price'=>'required|numeric|min:0',

            'products'=>'nullable|array',
            'products.*.id'=>'required',
            'products.*.product_share'=>'required|numeric|min:1|max:100',
            'products.*.name'=>[
                'exclude_unless:products.*.id,0',
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
