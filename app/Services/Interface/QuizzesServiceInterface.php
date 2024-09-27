<?php

namespace App\Services\Interface;

use App\DTOs\Quizz\CreateQuizzDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuizzesServiceInterface
{
    public function listQuizzes(): Collection|LengthAwarePaginator;

    public function createQuiz(CreateQuizzDTO $quizDTO, array $questionDTO): void;

    public function deleteQuiz(string $quizId): void;

    public function listQuestionOfQuiz(string $quizId): Collection;

    public function shareQuiz(string $quizId, string $email): void;
}
