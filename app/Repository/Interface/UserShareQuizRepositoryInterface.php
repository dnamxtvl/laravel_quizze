<?php

namespace App\Repository\Interface;

use App\DTOs\UserShareQuiz\CreateUserShareQuizDTO;
use App\Models\UserShareQuiz;
use Illuminate\Database\Eloquent\Builder;

interface UserShareQuizRepositoryInterface
{
    public function getQuery(array $columnSelects = [], array $filters = []): Builder;

    public function findAuthReceiver(array $filters = []): ?UserShareQuiz;

    public function createUserShareQuiz(CreateUserShareQuizDTO $userShareQuizDTO): UserShareQuiz;

    public function findByToken(string $token): ?UserShareQuiz;

    public function acceptShareQuiz(UserShareQuiz $userShareQuiz): void;

    public function rejectShareQuiz(UserShareQuiz $userShareQuiz): void;

    public function deleteShareQuiz(UserShareQuiz $userShareQuiz): void;
}
