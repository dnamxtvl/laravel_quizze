<?php

namespace App\DTOs\Quizz;

readonly class SaveSettingDTO
{
    public function __construct(
        private int $speedPriority,
        private ?array $backgrounds = null,
        private ?array $musics = null,
    ) {}

    public function getSpeedPriority(): int
    {
        return $this->speedPriority;
    }

    public function getBackgrounds(): ?array
    {
        return $this->backgrounds;
    }

    public function getMusics(): ?array
    {
        return $this->musics;
    }
}
