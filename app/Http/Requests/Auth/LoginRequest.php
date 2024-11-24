<?php
namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;//(Auth::attempt(['email'=>$this->email, 'password'=>Hash::make($this->password)], $this->boolean('remember')));
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Неверные данные входа: '.$this::class,
        ], 401));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
       return [
            'email' => 'required|email:rfc,dns|exists:users,email',
            'password' => 'required|min:8'
       ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Ошибки валидации',
            'errors'    => $validator->errors()
        ], 400));
    }
}
