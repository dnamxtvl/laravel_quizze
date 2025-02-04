<?php

namespace App\Http\Requests;

use App\Enums\Quiz\CreatedByEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchQuizzeRequest extends FormRequest
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
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['string', 'size:'. config(key: 'validation.max_length_uuid')],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:category,id'],
            'code' => ['nullable', 'string'],
            'start_time' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'end_time' => ['nullable', 'date', 'date_format:Y-m-d H:i:s', 'after:start_time'],
            'created_by' => ['nullable', 'integer', 'in:'.implode(',', array_map(fn ($enum) => $enum->value, CreatedByEnum::cases()))]
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.date_format' => 'Định dạng ngày không hợp lệ!',
            'end_time.date_format' => 'Định dạng ngày không hợp lệ!',
            'end_time.after' => 'Ngày kết thúc phải lớn hơn ngày bắt đầu!',
        ];
    }
}
