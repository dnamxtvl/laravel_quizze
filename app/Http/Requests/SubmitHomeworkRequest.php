<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SubmitHomeworkRequest extends FormRequest
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
            'results' => 'nullable|array',
            'results.*' => 'int',
            'list_question' => 'nullable|array',
            'list_question.*' => 'string|size:' . config('validation.max_length_uuid'),
            'list_answer' => 'nullable|array',
            'list_answer.*' => 'int',
            'auto_submit' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }

    #[NoReturn] protected function prepareForValidation(): void
    {
        if (is_null($this->input(key: 'results')) || !is_array($this->input(key: 'results'))) {
            throw new HttpResponseException(
                response: response()->json(
                    data: [
                        'message' => 'kết quả bài thi không hợp lệ!',
                    ],
                    status: ResponseAlias::HTTP_BAD_REQUEST
                ),
            );
        }

        $results = $this->input(key: 'results');
        asort($results);
        $this->merge([
            'list_question' => array_keys($results),
            'list_answer' => array_values($results),
        ]);
    }
}
