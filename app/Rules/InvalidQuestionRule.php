<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class InvalidQuestionRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (collect($value)->where('is_correct', true)->count() == 0) {
            $fail('Câu hỏi '.((int) explode('.', $attribute)[1] + 1).' phải có ít nhất 1 đáp án đúng');
        }
    }
}
