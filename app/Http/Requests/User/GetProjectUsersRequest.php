<?php
namespace App\Http\Requests\User;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Models\User\PermissionNames;
use App\Models\User\Permissions;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Rules\ProjectRules;


use function PHPUnit\Framework\isEmpty;

class GetProjectUsersRequest extends ChecksPermissionsRequest
{
    
    public function __construct() {
        parent::__construct([
            PermissionNames::READ_USERS->value,
            PermissionNames::CRUD_USERS->value
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            ProjectRules::projectRules()
        ];
    }
}
