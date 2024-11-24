<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Http\Rules\ProjectRules;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreProjectRequest extends FormRequest
{   
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            ProjectRules::storeProjectRules()
        );
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
