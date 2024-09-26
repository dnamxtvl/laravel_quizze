<?php

namespace App\Repository\Interface;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

interface QuestionRepositoryInterface
{
    public function listQuestion(array $columnSelects = [], array $filters = []): Collection;

    public function findNextQuestion(string $quzId, string $questionId): ?Question;

    public function insertQuestions(array $questions, string $quizId): array;

    public function deleteQuestion(string $quizId): void;

    public function listQuestionOfQuiz(string $quizId): Collection;

    public function listQuestionByIds(array $questionIds): Collection;
}
