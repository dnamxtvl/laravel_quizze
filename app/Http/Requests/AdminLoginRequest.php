<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|min:'.config('validation.email.min_length').'|max:'.config('validation.email.max_length'),
            'password' => 'required|string|min:'.config('validation.password.min_length').'|max:'.config('validation.password.max_length'),
            'remember_me' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không vượt quá '.config('validation.email.max_length').' ký tự',
            'email.min' => 'Email không ít hơn '.config('validation.email.min_length').' ký tự',
            'password.required' => 'Mật khẩu không được để trống',
            'password.string' => 'Mật khẩu phải là chuỗi',
            'password.min' => 'Mật khẩu không được ít hơn '.config('validation.password.min_length').' ký tự',
            'password.max' => 'Mật khẩu không được quá '.config('validation.password.max_length').' ký tự',
            'remember_me.boolean' => 'Remember me phải là boolean',
        ];
    }
}
