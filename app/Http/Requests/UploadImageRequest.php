<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    const MAX_SIZE = 2048;
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
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:' . self::MAX_SIZE],
        ];
    }

    public function messages(): array
    {
        return [
            'image.mimes' => 'Ảnh phải có định dạng là jpeg, png, jpg, gif',
            'image.max' => 'Dung lượng ảnh không được vượt quá 5MB',
        ];
    }
}
