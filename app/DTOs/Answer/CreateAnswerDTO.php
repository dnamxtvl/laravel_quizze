<?php

namespace App\DTOs\Answer;

class CreateAnswerDTO
{
    public function __construct(
        private readonly string $answer,
        private readonly bool $isCorrect,
        private ?string $questionId = null,
    ) {}

    public function getQuestionId(): ?string
    {
        return $this->questionId;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getIsCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setQuestionId(string $questionId): void
    {
        $this->questionId = $questionId;
    }
}
