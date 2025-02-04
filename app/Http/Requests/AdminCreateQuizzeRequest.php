<?php

namespace App\Http\Requests;

use App\Rules\InvalidQuestionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminCreateQuizzeRequest extends FormRequest
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
            'quizze.title' => 'required|string|max:255',
            'quizze.category_id' => 'required|integer|exists:category,id',
            'questions' => 'required|array|min:1',
            'questions.*.title' => 'required|string|max:10000',
            'questions.*.image' => 'nullable|url:http,https',
            'questions.*.time_reply' => 'nullable|numeric|min:' . config('app.quizzes.min_time_reply') . '|max:' . config('app.quizzes.max_time_reply'),
            'questions.*.answers' => ['required', 'array', 'min:1', new InvalidQuestionRule],
            'questions.*.answers.*.answer' => 'required|string|max:255',
            'questions.*.answers.*.is_correct' => 'required|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $questions = $this->input('questions', []);

        foreach ($questions as $qIndex => $question) {
            if (isset($question['answers'])) {
                foreach ($question['answers'] as $aIndex => $answer) {
                    $questions[$qIndex]['answers'][$aIndex]['is_correct'] = filter_var($answer['is_correct'], FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        $this->merge(['questions' => $questions]);
    }

    public function messages(): array
    {
        return [
            'quizze.title.required' => 'Tiêu đề đang để trống',
            'quizze.title.string' => 'Tiêu đề phải là chuỗi',
            'quizze.title.max' => 'Tiêu đề không được quá 255 ký tự',
            'quizze.category_id.required' => 'Danh mục không được để trống',
            'quizze.category_id.integer' => 'Danh mục phải là số nguyên',
            'quizze.category_id.exists' => 'Danh mục không tồn tại',
            'questions.required' => 'Câu hỏi không được để trống',
            'questions.array' => 'Câu hỏi phải là mảng',
            'questions.min' => 'Câu hỏi phải có ít nhất một câu hỏi',
            'questions.*.title.required' => 'Tiêu đề câu hỏi không được để trống',
            'questions.*.title.string' => 'Tiêu đề câu hỏi phải là chuỗi',
            'questions.*.title.max' => 'Tiêu đề câu hỏi không được quá 10000 ký tự',
            'questions.*.answers.required' => 'Câu trả lời không được để trống',
            'questions.*.answers.array' => 'Câu trả lời phải là mảng',
            'questions.*.answers.min' => 'Câu trả lời phải có ít nhất một câu trả lời',
            'questions.*.answers.*.answer.required' => 'Câu trả lời không được để trống',
            'questions.*.answers.*.answer.string' => 'Câu trả lời phải là chuỗi',
            'questions.*.answers.*.answer.max' => 'Câu trả lời không được quá 255 ký tự',
            'questions.*.answers.*.is_correct.required' => 'Câu trả lời đúng không được để trống',
            'questions.*.answers.*.is_correct.boolean' => __('is_correct_boolean'),
            'questions.*.image.url' => 'Link ảnh không hợp lệ!',
            'questions.*.time_reply.required' => 'Thoi gian trả lời đang để trống!',
            'questions.*.time_reply.min' => 'Thời gian trả lời phải lớn hơn ' . config('app.quizzes.min_time_reply') . ' và nhỏ hơn ' . config('app.quizzes.max_time_reply') . ' giây',
            'questions.*.time_reply.max' => 'Thời gian trả lời phải lớn hơn ' . config('app.quizzes.min_time_reply') . ' và nhỏ hơn ' . config('app.quizzes.max_time_reply') . ' giây',
        ];
    }
}
