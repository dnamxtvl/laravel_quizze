<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'email' => 'required|email|min:'.config('validation.email.min_length').'|max:'.config('validation.email.max_length'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email đang để trống',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không vượt quá '.config('validation.email.max_length').' ký tự',
            'email.min' => 'Email không ít hơn '.config('validation.email.min_length').' ký tự',
        ];
    }
}
