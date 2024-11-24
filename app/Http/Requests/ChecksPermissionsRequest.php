<?php

namespace App\Http\Requests;

use App\Http\Rules\ProjectRules;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ChecksPermissionsRequest extends FormRequest
{
    private $permissions;

    public function __construct(array $permissions) {
        $this->permissions = $permissions;
    }
    
    protected function prepareForValidation() 
    {
        $this->merge(['project_id' => $this->route('project_id')]);
    }
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $validator = Validator::make($this->all(), ProjectRules::projectRules());
 
        if ($validator->fails()) {
            $this->failedValidation($validator );
        }
        $user = User::find(Auth::user()?->id);
        return empty($user) ? false : $user->hasAnyPermission($this->project_id, 
            $this->permissions
        );
    }

     public function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Нет прав доступа: '.$this::class,
        ], 403));
    }

    public function failedValidation(ValidationValidator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Ошибки валидации: '.$validator->errors()->first(),
            'errors'      => $validator->errors()
        ], 400));
    }

}
