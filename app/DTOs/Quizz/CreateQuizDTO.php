<?php

namespace App\DTOs\Quizz;

class CreateQuizDTO
{
    public function __construct(
        private readonly string $title,
        private readonly int $categoryId,
        private readonly string $userId,
        private readonly bool $createdBySys = false,
        private string $code = '',
    ) {}

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

    public function getCreatedBySys(): bool
    {
        return $this->createdBySys;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
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
