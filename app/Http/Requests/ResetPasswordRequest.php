<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'user_id' => ['required', 'string', 'size:' . config('validation.max_length_uuid')],
            'token' => 'required',
            'password' => 'required|min:' . config('validation.password.min_length') . '|max:' . config('validation.password.max_length'),
            'password_confirmation' => 'required|same:password',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Token không hợp lệ!',
            'user_id.size' => 'Token không hợp lệ!',
            'token.required' => 'Token không hợp lệ!',
            'password.required' => 'Mật khẩu đang để trống!',
            'password.min' => 'Mật khẩu phải lớn hơn ' . config('validation.password.min_length') . ' ký tự!',
            'password.max' => 'Mật khẩu phải lnhỏ hơn ' . config('validation.password.max_length') . ' ký tự!',
            'password_confirmation.required' => 'Mật không hợp lệ!',
            'password_confirmation.same' => 'Mật khẩu nhập lại không chính xác!',
        ];
    }
}
