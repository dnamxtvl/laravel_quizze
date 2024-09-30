<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShareQuizRequest extends FormRequest
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
            'notification_id' => 'nullable|string|size:' . config(key: 'app.notify.notify_id_length'),
        ];
    }

    public function messages(): array
    {
        return [
            'notification_id.size' => 'Thông báo không hợp lệ',
            'notification_id.string' => 'Thông báo không hợp lệ',
        ];
    }
}
