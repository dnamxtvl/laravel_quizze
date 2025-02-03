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
            'title' => 'required|string|max:10000',
            'answers' => ['required', 'array', 'min:'.config(key: 'app.question.min_answers'), 'max:'.config(key: 'app.question.max_answers'), new InvalidQuestionRule],
            'answers.*.answer' => 'required|string|max:'.config(key: 'app.question.answer.max_length'),
            'answers.*.is_correct' => 'required|boolean',
            'image' => 'nullable|url:http,https',
            'time_reply' => 'required|numeric|min:' . config('app.quizzes.min_time_reply') . '|max:' . config('app.quizzes.max_time_reply'),
        ];
    }

    public function prepareForValidation(): void
    {
        $answers = $this->input('answers', []);
        if (!empty($answers)) {
            foreach ($answers as $key => &$answer) {
                $answers[$key]['is_correct'] = filter_var($answer['is_correct'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        $this->merge(['answers' => $answers]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề đang trống',
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề quá dàị',
            'answers.required' => 'Câu trả lời đang trống',
            'answers.array' => 'Câu trả lời phải là mảng!',
            'answers.min' => 'Câu trả lời phải có ít nhất :min đáp án!',
            'answers.max' => 'Câu trả lời phải ít hơn :max đáp án!',
            'answers.*.answer.required' => 'Câu trả lời đang trống',
            'answers.*.answer.string' => 'Câu trả lời phải là chuỗi',
            'answers.*.answer.max' => 'Câu trả lời phải ít hơn :max ký tự',
            'answers.*.is_correct.required' => 'Câu trả lời không đúng định dạng',
            'image.url' => 'Link ảnh không hợp lệ!',
            'time_reply.required' => 'Thoi gian trả lời đang để trống!',
            'time_reply.min' => 'Thời gian trả lời phải lớn hơn ' . config('app.quizzes.min_time_reply') . ' và nhỏ hơn ' . config('app.quizzes.max_time_reply') . ' giây',
            'time_reply.max' => 'Thời gian trả lời phải lớn hơn ' . config('app.quizzes.min_time_reply') . ' và nhỏ hơn ' . config('app.quizzes.max_time_reply') . ' giây',
        ];
    }
}
