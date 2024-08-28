<?php

namespace App\DTOs\Gamer;

readonly class SaveAnswerDTO
{
    public function __construct(
        private string $gamerId,
        private string $questionId,
        private int $answerId,
        private string $roomId,
        private int $answerInTime,
        private int $score,
    ) {}

    public function getGamerId(): string
    {
        return $this->gamerId;
    }

    public function getQuestionId(): string
    {
        return $this->questionId;
    }

    public function getAnswerId(): int
    {
        return $this->answerId;
    }

    public function getAnswerInTime(): int
    {
        return $this->answerInTime;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getRoomId(): string
    {
        return $this->roomId;
    }
}
