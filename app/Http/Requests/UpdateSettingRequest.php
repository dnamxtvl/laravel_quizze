<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    const MAX_UPLOAD_SIZE = 5120;
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
            'quizze_ids' => ['required', 'array'],
            'quizze_ids.*' => ['required', 'string', 'uuid'],
            'speed_priority' => ['required', 'integer', 'min:' . config(key: 'app.question.speed_priority.min'), 'max:' . config(key: 'app.question.speed_priority.max')],
            'background' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:' . self::MAX_UPLOAD_SIZE],
            'music' => ['nullable', 'file', 'mimes:mp3,m4a,aac,ogg,wav,flac,webm', 'max:' . self::MAX_UPLOAD_SIZE],
        ];
    }

    public function messages(): array
    {
        return [
            'speed_priority.required' => 'Trọng số ưu tiên đang để trống!',
            'speed_priority.integer' => 'Trọng số ưu tiên không hợp lệ!',
            'speed_priority.min' => 'Trọng số ưu tiên không hợp lệ!',
            'speed_priority.max' => 'Trọng số ưu tiên không hợp lệ!',
            'background.file' => 'Hình nền tải lên không hợp lệ!',
            'background.mimes' => 'Hình nền tải lên không hợp lệ!',
            'background.max' => 'Hình nền tải lên phải nhỏ hơn ' . self::MAX_UPLOAD_SIZE . 'KB!',
            'music.mimes' => 'Nhạc nền tải lên không hợp lệ!',
            'music.file' => 'Nhạc nền tải lên không hợp lệ!',
            'music.max' => 'Nhạc nền tải lên phải nhỏ hơn ' . self::MAX_UPLOAD_SIZE . 'KB!',
            'quizze_ids.array' => 'Câu hỏi áp dụng không hợp lệ!',
            'quizze_ids.*.required' => 'Câu hỏi áp dụng không hợp lệ!',
            'quizze_ids.*.string' => 'Câu hỏi áp dụng không hợp lệ!',
            'quizze_ids.*.uuid' => 'Câu hỏi áp dụng không hợp lệ!',
        ];
    }
}
