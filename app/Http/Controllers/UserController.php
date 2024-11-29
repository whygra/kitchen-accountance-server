<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\AssignUserRoleRequest;
use App\Http\Requests\User\InviteUserRequest;
use App\Models\User\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\AssignUserRolesRequest;
use App\Http\Requests\User\GetProjectUsersRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\ProjectUserResource;
use App\Http\Resources\User\UserResource;
use App\Models\Project;
use App\Models\User\Role;
use App\Models\User\RoleNames;
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

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProjectUsersRequest $request)
    {
        $all = Project::find($request->project_id)->users()->get();
        return response()->json(ProjectUserResource::collection($all));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = User::find($id);
        return response()->json($item);
    }

    public function assign_role(AssignUserRoleRequest $request, $project_id, $id)
    {
        $user = User::find($id);
        if(empty($user))
            return response()->json([
                'message' => ''
            ], 404);

        $project = $user->projects()->find($project_id);

        if(empty($project))
            return response()->json([
                'message' => ''
            ], 404);

        $user->role()->associate($request->role['id']);

        $user->save();
        return response()->json(new UserResource($user), 200);
    }

    public function invite_user(InviteUserRequest $request, $project_id)
    {
        $user = User::where($request->email);
        if(empty($user))
            return response()->json([
                'message' => ''
            ], 404);

        $project = $user->projects()->find($project_id);

        if(empty($project))
            return response()->json([
                'message' => ''
            ], 404);

        $user->role()->associate(Role::where('name', RoleNames::VIEWER->value));

        $user->save();
        return response()->json(new UserResource($user), 200);
    }

    public function get_roles(GetProjectUsersRequest $request)
    {
        return response()->json(Role::with('permissions')->get(), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        $item = User::find(id: Auth::user()->id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->delete();
        return response()->json($item, 200);
    }
}
