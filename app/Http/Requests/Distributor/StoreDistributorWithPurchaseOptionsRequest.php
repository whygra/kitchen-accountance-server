<?php

namespace App\Http\Requests\Distributor;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

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
            'purchase_options'=>'array',
            'purchase_options.*.id'=>'required|exists:purchase_options,id',
            'purchase_options.*.product_share'=>'required|numeric|min:1|max:100',
            'purchase_options.*.products'=>'array',
            'purchase_options.*.products.*.id'=>'required',
            'purchase_options.*.products.*.name'=>'required|string',
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
