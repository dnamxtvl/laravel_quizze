<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:'.config('validation.username.max_length'), 'min:'.config('validation.username.min_length')],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Username không được để trống',
            'name.string' => 'Username phải là chuỗi',
            'name.max' => 'Username không được quá '.config('validation.username.max_length').' ký tự',
            'name.min' => 'Username không ít hơn '.config('validation.username.min_length').' ký tự',
            'name.mimes' => 'Avatar phải có định dạng là jpeg, png, jpg, gif',
        ];
    }
}
