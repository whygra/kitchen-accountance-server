<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use App\Http\Rules\ProjectRules;


class UploadProjectBackdropImageRequest extends FormRequest
{
    
    public function authorize(): bool {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'file'=>[
                'required', 
                'image:jpeg,png,jpg,svg',
                'max:2048'
            ]
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Ошибки валидации: '.$validator->errors()->first(),
            'errors'    => $validator->errors()
        ], 400));
    }
}
