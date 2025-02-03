<?php

namespace App\Services\Interface;

use App\DTOs\Question\CreateQuestionDTO;
use App\Models\Question;
use Carbon\Carbon;

interface QuestionServiceInterface
{
    public function updateQuestion(string $questionId, CreateQuestionDTO $questionDTO): void;

    public function addQuestion(string $quizId, CreateQuestionDTO $questionDTO): Question;

    public function deleteQuestion(string $questionId): void;

    public function countQuestion(): int;

    public function countAnswerByTime(Carbon $startTime, Carbon $endTime): array;
}
