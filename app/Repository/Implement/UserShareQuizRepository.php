<?php

namespace App\Repository\Implement;

use App\DTOs\UserShareQuiz\CreateUserShareQuizDTO;
use App\Models\UserShareQuiz;
use App\Pipeline\Global\QuizzIdFilter;
use App\Pipeline\UserShareQuestion\ReceiverFilter;
use App\Repository\Interface\UserShareQuizRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

readonly class UserShareQuizRepository implements UserShareQuizRepositoryInterface
{
    public function __construct(
        private UserShareQuiz $userShareQuestion
    ) {}

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->userShareQuestion->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new QuizzIdFilter(filters: $filters),
                new ReceiverFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function findAuthReceiver(array $filters = []): ?UserShareQuiz
    {
        return $this->getQuery(filters: $filters)
            ->where('is_accept', true)
            ->first();
    }

    public function createUserShareQuiz(CreateUserShareQuizDTO $userShareQuizDTO): UserShareQuiz
    {
        $userShareQuiz = new UserShareQuiz();
        $userShareQuiz->user_share_id = $userShareQuizDTO->getUserShareId();
        $userShareQuiz->quizze_id = $userShareQuizDTO->getQuizId();
        $userShareQuiz->receiver_id = $userShareQuizDTO->getReceiverId();
        $userShareQuiz->token = $userShareQuizDTO->getToken();
        $userShareQuiz->save();

        return $userShareQuiz;
    }

    public function findByToken(string $token): ?UserShareQuiz
    {
        return $this->userShareQuestion->query()
            ->with('quiz')
            ->where('token', $token)
            ->first();
    }

    public function acceptShareQuiz(UserShareQuiz $userShareQuiz): void
    {
        $userShareQuiz->is_accept = true;
        $userShareQuiz->accepted_at = now();
        $userShareQuiz->save();
    }

    public function rejectShareQuiz(UserShareQuiz $userShareQuiz): void
    {
        $userShareQuiz->delete();
    }

    public function deleteShareQuiz(UserShareQuiz $userShareQuiz): void
    {
        $userShareQuiz->delete();
    }
}
