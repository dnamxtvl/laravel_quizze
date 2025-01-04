<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:'.config('validation.username.max_length'), 'min:'.config('validation.username.min_length')],
            'email' => 'required|string|email|min:'.config('validation.email.min_length').'|max:'.config('validation.email.max_length').'|unique:users,email',
            'password' => 'required|string|min:'.config('validation.password.min_length').'|max:'.config('validation.password.max_length'),
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username không được để trống',
            'username.string' => 'Username phải là chuỗi',
            'username.max' => 'Username không được quá '.config('validation.user_name.max_length').' ký tự',
            'username.min' => 'Username không ít hơn '.config('validation.user_name.min_length').' ký tự',
            'email.required' => 'Email không được để trống',
            'email.string' => 'Email phải là chuỗi',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không vượt quá '.config('validation.email.max_length').' ký tự',
            'email.min' => 'Email không ít hơn '.config('validation.email.min_length').' ký tự',
            'email.unique' => 'Email đã tồn tại',
            'password.required' => 'Mật khẩu không được để trống',
            'password.string' => 'Mật khẩu phải là chuỗi',
            'password.min' => 'Mật khẩu không được ít hơn '.config('validation.password.min_length').' ký tự',
            'password.max' => 'Mật khẩu không được quá '.config('validation.password.max_length').' ký tự',
        ];
    }
}
