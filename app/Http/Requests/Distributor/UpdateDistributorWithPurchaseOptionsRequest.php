<?php

namespace App\Http\Requests\Distributor;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateDistributorWithPurchaseOptionsRequest extends FormRequest
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
            'id'=>'required|exists:distributors,id',
            'name'=>'required|string|max:60|unique:distributors,name,'.$this['id'],

            'purchase_options'=>'nullable|array',
            'purchase_options.*.id'=>'required',
            'purchase_options.*.name'=>[
                'exclude_unless:purchase_options.*.id,0',
                'string',
                'max:120',
                'distinct:ignore_case',
                Rule::unique('purchase_options', 'name')->where('distributor_id', $this['id']),
            ],
            
            'purchase_options.*.unit.id'=>'required',
            'purchase_options.*.unit.long'=>[
                'exclude_unless:purchase_options.unit.id,0',
                'string',
                'max:60',
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

            'purchase_options.*.products'=>'nullable|array',
            'purchase_options.*.products.*.id'=>'required',
            'purchase_options.*.products.*.name'=>[
                'exclude_unless:purchase_options.*.products.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                'unique:products,name',
            ],
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
