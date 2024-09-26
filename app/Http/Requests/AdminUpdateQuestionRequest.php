<?php

namespace App\Http\Requests;

class AdminUpdateQuestionRequest extends AdminCreateQuestionRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
