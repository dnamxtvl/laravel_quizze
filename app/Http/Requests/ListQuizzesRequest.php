<?php

namespace App\Http\Requests;

use App\Enums\Quiz\TypeQuizEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListQuizzesRequest extends FormRequest
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
            'type' => 'nullable|in:'.implode(',', array_map(fn ($enum) => $enum->value, TypeQuizEnum::cases())),
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Type quiz không hợp lệ!',
        ];
    }
}
