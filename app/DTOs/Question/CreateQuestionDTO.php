<?php

namespace App\DTOs\Question;

class CreateQuestionDTO
{
    public function __construct(
        private readonly string $title,
        private array $answers,
        private ?string $quizId = null,
    ) {
    }

    public function getQuizId(): string
    {
        return $this->quizId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setQuizId(string $quizId): void
    {
        $this->quizId = $quizId;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }
}
