<?php

namespace App\Http\Requests;

use App\Enums\User\UserRoleEnum;

class AdminCreateUserRequest extends RegisterRequest
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
        return array_merge(
            parent::rules(),
            [
                'type' => 'required|in:' . implode(',', UserRoleEnum::toArrayValue()),
                'avatar' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            [
                'type.required' => 'Bạn chưa chọn loại tài khoản',
                'type.in' => 'Loại tài khoản không hợp lệ',
            ]
        );
    }
}
