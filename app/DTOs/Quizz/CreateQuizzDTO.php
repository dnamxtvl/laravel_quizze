<?php

namespace App\DTOs\Quizz;

readonly class CreateQuizzDTO
{
    public function __construct(
        private string $title,
        private int $categoryId,
        private string $userId,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category_id' => $this->categoryId,
            'user_id' => $this->userId,
        ];
    }
}
