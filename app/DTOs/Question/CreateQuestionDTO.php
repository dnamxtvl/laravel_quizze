<?php

namespace App\DTOs\Question;

class CreateQuestionDTO
{
    public function __construct(
        private readonly string $title,
        private array $answers,
        private readonly ?string $image = null,
        private readonly int $timeReply,
        private ?string $quizId = null,
    ) {}

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

    /**
     * @return int
     */
    public function getTimeReply(): int
    {
        return $this->timeReply;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }
}
