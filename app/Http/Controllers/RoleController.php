<?php

namespace App\Http\Controllers;

use App\Models\User\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\AssignUserRolesRequest;
use App\Http\Requests\User\GetProjectUsersRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\RoleResource;
use App\Http\Resources\User\UserResource;
use App\Models\User\Permission;
use App\Models\User\Role;
use Error;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{

    public function all(GetProjectUsersRequest $request)
    {
        return response()->json(RoleResource::collection(Role::with('permissions')->get()), 200);
    }


    public function permissions(GetProjectUsersRequest $request)
    {
        return response()->json(Permission::with('roles')->get(), 200);
    }

}
