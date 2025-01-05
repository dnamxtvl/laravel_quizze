<?php

namespace App\Http\Requests;

use App\Enums\User\UserRoleEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\RequiredIf;

class ChangePasswordRequest extends FormRequest
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
            'old_password' => [new RequiredIf(Auth::user()->role != UserRoleEnum::SYSTEM->value), 'string', 'min:'.config('validation.password.min_length'), 'max:'.config('validation.password.max_length')],
            'new_password' => 'required|string|min:'.config('validation.password.min_length').'|max:'.config('validation.password.max_length'),
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'Mật khẩu không được để trống',
            'old_password.string' => 'Mật khẩu phải là chuỗi',
            'old_password.min' => 'Mật khẩu không được ít hơn '.config('validation.password.min_length').' ký tự',
            'old_password.max' => 'Mật khẩu không được quá '.config('validation.password.max_length').' ký tự',
            'new_password.required' => 'Mật khẩu không được để trống',
            'new_password.string' => 'Mật khẩu phải là chuỗi',
            'new_password.min' => 'Mật khẩu không được ít hơn '.config('validation.password.min_length').' ký tự',
            'new_password.max' => 'Mật khẩu không được quá '.config('validation.password.max_length').' ký tự',
        ];
    }
}
