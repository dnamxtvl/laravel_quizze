<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NextQuestionRequest extends FormRequest
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
            'question_id' => 'required|string',
            'room_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.required' => 'Question id không được để trống',
            'question_id.string' => 'Question id phải là chuỗi',
            'room_id.required' => 'Room id không được để trống',
            'room_id.string' => 'Room id phải là chuỗi',
        ];
    }
}
