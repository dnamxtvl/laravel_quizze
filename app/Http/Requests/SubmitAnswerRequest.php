<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
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
            'answer_id' => 'required|int',
            'token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'answer_id.required' => 'Answer id không được để trống',
            'answer_id.int' => 'Answer id phải là số nguyên',
            'token.required' => 'Token không được để trống',
            'token.string' => 'Token phải là chuỗi',
        ];
    }
}
