<?php

namespace App\Http\Requests;

use App\Rules\InvalidQuestionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminCreateQuestionRequest extends FormRequest
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
            'title' => 'required|string|min:' . config(key: 'app.question.title.min_length') . '|max:' . config(key: 'app.question.title.max_length'),
            'answers' => ['required', 'array', 'min:' . config(key: 'app.question.min_answers'), 'max:' . config(key: 'app.question.max_answers'), new InvalidQuestionRule()],
            'answers.*.answer' => 'required|string|max:' . config(key: 'app.question.answer.max_length'),
            'answers.*.is_correct' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề đang trống',
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.min' => 'Tiêu đề phải nhiều hơn :min ký tự',
            'title.max' => 'Tiêu đề phải ít hơn :max ký tự',
            'answers.required' => 'Câu trả lời đang trống',
            'answers.array' => 'Câu trả lời phải là mảng!',
            'answers.min' => 'Câu trả lời phải có ít nhất :min đáp án!',
            'answers.max' => 'Câu trả lời phải ít hơn :max đáp án!',
            'answers.*.answer.required' => 'Câu trả lời đang trống',
            'answers.*.answer.string' => 'Câu trả lời phải là chuỗi',
            'answers.*.answer.max' => 'Câu trả lời phải ít hơn :max ký tự',
            'answers.*.is_correct.required' => 'Câu trả lời không đúng định dạng',
        ];
    }
}
