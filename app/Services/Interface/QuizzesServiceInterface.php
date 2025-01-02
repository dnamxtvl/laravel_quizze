<?php

namespace App\Services\Interface;

use App\DTOs\Quizz\CreateQuizDTO;
use App\DTOs\Quizz\SearchQuizDTO;
use App\Enums\Quiz\TypeQuizEnum;
use App\Models\UserShareQuiz;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuizzesServiceInterface
{
    public function listQuizzes(TypeQuizEnum $type): Collection|LengthAwarePaginator;

    public function createQuiz(CreateQuizDTO $quizDTO, array $questionDTO): void;

    public function deleteQuiz(string $quizId): void;

    public function listQuestionOfQuiz(string $quizId): Collection;

    public function shareQuiz(string $quizId, string $email): void;

    public function acceptShareQuiz(string $token, ?string $notifyId = null): void;

    public function detailShareQuiz(string $token, ?string $notifyId = null): UserShareQuiz;

    public function rejectShareQuiz(string $token, ?string $notifyId = null): void;

    public function searchQuiz(SearchQuizDTO $searchQuizDTO): LengthAwarePaginator;
}
