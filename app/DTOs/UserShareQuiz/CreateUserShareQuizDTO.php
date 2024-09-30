<?php

namespace App\DTOs\UserShareQuiz;

readonly class CreateUserShareQuizDTO
{
    public function __construct(
        private string $userShareId,
        private string $quizId,
        private string $receiverId,
        private string $token,
    ) {}

    public function getUserShareId(): string
    {
        return $this->userShareId;
    }

    public function getQuizId(): string
    {
        return $this->quizId;
    }

    public function getReceiverId(): string
    {
        return $this->receiverId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function toArray(): array
    {
        return [
            'user_share_id' => $this->getUserShareId(),
            'quiz_id' => $this->getQuizId(),
            'receiver_id' => $this->getReceiverId(),
        ];
    }
}
