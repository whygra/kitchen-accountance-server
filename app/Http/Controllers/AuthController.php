<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\AssignUserRolesRequest;
use App\Http\Requests\User\GetProjectUsersRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use Error;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);
        event(new Registered($user));

        $user->sendEmailVerificationNotification();

        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'message' => 'Учетная запись создана',
        ]);
    }
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email',$request['email'])->first();
        if(!Hash::check($request->password, $user->password))
            throw new HttpResponseException(response()->json([
                'success'   => false,
                'message'   => 'Неверные данные входа: '.$this::class,
            ], 401));

        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;
        return response()->json([
            'access_token' => $token,
        ]);

    }
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()->delete(); 
        
        return response()->json([
            'message' => 'Выполнен выход',
        ]);
    }

    public function authorization_needed() {
        return response()->json([
            'message' => 'для доступа к ресурсу требуется авторизоваться'
        ], 401);
    } 

    public function verification_needed() {
        return response()->json([
            'message' => 'необходимо подтвердить адрес электронной почты'
        ], 401);
    } 

    public function verify(EmailVerificationRequest $request) {

        
        // if (!$request->hasValidSignature() || !$request->user()->id == $user_id) {
        //     return response()->json(["message" => "Некорректная/устаревшая ссылка"], 401);
        // }
    
        // $user = User::findOrFail($user_id);
    
        // if($user->hasVerifiedEmail()){
        //     return response()->json(['message'=>'email уже подтвержден']);
        // }

        $request->fulfill();
        
        return response()->json(['message'=>'Адрес почты подтвержден']);
    }
    
    public function resend() {
        $user = User::find(Auth::user()->id);

        if ($user->hasVerifiedEmail()) {
            return response()->json(["message" => "Email уже подтвержден"], 400);
        }
    
        $user->sendEmailVerificationNotification();
    
        return response()->json(["message" => "Ссылка отправлена на адрес ".$user->email]);
    }

    public function current()
    { 
        $user = User::find(Auth::user()->id);
        return response()->json(new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request)
    {
        $item = User::find(Auth::user()->id);
        if(empty($item))
            return response()->json([
                'message' => 'пользователь не найден'
            ], 404);

        if($item->is_superuser)
            return response()->json([
                'message' => 'Изменение данных суперпользователя запрещено'
            ],400);
            
        if(!empty($request->name))
            $item->name = $request->name;
        if(!empty($request->email))
            $item->email = $request->email;

        $item->save();
        return response()->json($item, 204);
    }
    
    public function update_password(\App\Http\Requests\Auth\UpdatePasswordRequest $request)
    {
        $item = User::find(Auth::user()->id);
        if(empty($item))
            return response()->json([
                'message' => 'пользователь не найден'
            ], 404);

        if(!$item || !Hash::check($request['password'],$item->password))
            return response()->json([
                'message' => 'Неверный пароль'
            ],401);

        $item->password = Hash::make($request->new_password);

        $item->save();
        return response()->json($item, 204);
    }

    public function get_roles(GetProjectUsersRequest $request)
    {
        return response()->json(Role::with('permissions')->get(), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = User::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->delete();
        return response()->json($item, 200);
    }
}
