<?php

namespace App\Http\Requests;

use App\Http\Rules\ProjectRules;
use App\Models\Project;
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
            $this->failedValidation($validator);
        }
        $project = Project::find($this->project_id);
        $user = Auth::user() ? User::find(Auth::user()->id) : null;
        // подставить гостя, если проект - образец и пользователь не авторизован
        if($project->is_public && empty($user)){
            $user = User::guest();
        }
        return $user?->hasAnyPermission($this->project_id, 
            $this->permissions
        ) ?? false;
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
        $key = $validator->errors()->keys()[0];
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => "Ошибки валидации: $key - ".$validator->errors()->first($key).' и ещё '.$validator->errors()->count(),
            'errors'      => $validator->errors()
        ], 400));
    }

}
