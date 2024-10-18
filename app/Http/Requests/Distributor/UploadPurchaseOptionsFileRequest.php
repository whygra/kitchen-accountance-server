<?php

namespace App\Http\Requests\Distributor;

use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UploadPurchaseOptionsFileRequest extends FormRequest
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

    protected function prepareForValidation():void
    {
        $this->merge([
            'column_indexes' => json_decode($this->column_indexes, true),
            'id' => json_decode($this->id),
        ]);
    }

    public function rules(): array
    {
        return [
            'id'=>'required|exists:distributors,id',

            'column_indexes'=>'required|array|required_array_keys:name,price',
            'file'=>[
                'required', 
                File::types(['xlsx', 'csv'])
            ],
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Ошибки валидации'.$validator->errors()->first(),
            'errors'    => $validator->errors()
        ], 400));
    }
}
