<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
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
            'quizze_ids' => ['required', 'array'],
            'quizze_ids.*' => ['required', 'string', 'uuid'],
            'speed_priority' => ['required', 'integer', 'min:' . config(key: 'app.question.speed_priority.min'), 'max:' . config(key: 'app.question.speed_priority.max')],
            'background' => ['nullable', 'array', 'min:'.config(key: 'app.question.background.min'), 'max:'.config(key: 'app.question.background.max')],
            'background.*' => ['required', 'url'],
            'music' => ['nullable', 'array', 'min:'.config(key: 'app.question.music.min'), 'max:'.config(key: 'app.question.music.max')],
            'music.*' => ['required', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'speed_priority.required' => 'Trọng số ưu tiên đang để trống!',
            'speed_priority.integer' => 'Trọng số ưu tiên không hợp lệ!',
            'speed_priority.min' => 'Trọng số ưu tiên không hợp lệ!',
            'speed_priority.max' => 'Trọng số ưu tiên không hợp lệ!',
            'background.array' => 'Hình nền tải lên không hợp lệ!',
            'background.*.min' => 'Hình nền tải lên không hợp lệ!',
            'background.*.max' => 'Hình nền tải lên không hợp lệ!',
            'background.*.required' => 'Hình nền tải lên không hợp lệ!',
            'background.*.url' => 'Hình nền tải lên không hợp lệ!',
            'music.array' => 'Nhạc nền tải lên không hợp lệ!',
            'music.*.required' => 'Nhạc nền tải lên không hợp lệ!',
            'music.*.url' => 'Nhạc nền tải lên không hợp lệ!',
            'music.*.min' => 'Nhạc nền tải lên không hợp lệ!',
            'music.*.max' => 'Nhạc nền tải lên không hợp lệ!',
            'quizze_ids.array' => 'Câu hỏi áp dụng không hợp lệ!',
            'quizze_ids.*.required' => 'Câu hỏi áp dụng không hợp lệ!',
            'quizze_ids.*.string' => 'Câu hỏi áp dụng không hợp lệ!',
            'quizze_ids.*.uuid' => 'Câu hỏi áp dụng không hợp lệ!',
        ];
    }
}
