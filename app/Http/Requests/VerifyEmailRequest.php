<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
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
            'otp_id' => 'required|string|size:'.config('validation.max_length_uuid'),
            'token' => 'required|string',
            'verify_code' => 'required|string|size:'.config('validation.verify_code.length'),
        ];
    }

    public function messages(): array
    {
        return [
            'otp_id.required' => 'Token không hợp lệ!',
            'otp_id.string' => 'Token không hợp lệ',
            'token.required' => 'Token không hợp lệ!',
            'token.string' => 'Token không hợp lệ',
            'user_id.max' => 'UserId không vượt quá '.config('validation.max_length_uuid').' ký tự',
            'verify_code.required' => 'Code xác thực đang để trống',
            'verify_code.size' => 'Code xác thực phải '.config('validation.length_of_verify_code').' ký tự',
        ];
    }
}
