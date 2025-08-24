<?php

namespace App\DTOs\Quizz;

use Illuminate\Http\UploadedFile;

class SaveSettingDTO
{
    public function __construct(
        private readonly int $speedPriority,
        private readonly ?UploadedFile $backgroundFile = null,
        private readonly ?UploadedFile $musicFile = null,
        private ?string $background = null,
        private ?string $music = null,
    ) {}

    public function getSpeedPriority(): int
    {
        return $this->speedPriority;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(string $background): void
    {
        $this->background = $background;
    }

    public function getMusic(): ?string
    {
        return $this->music;
    }

    public function setMusic(string $music): void
    {
        $this->music = $music;
    }

    public function getBackgroundFile(): ?UploadedFile
    {
        return $this->backgroundFile;
    }

    public function getMusicFile(): ?UploadedFile
    {
        return $this->musicFile;
    }
}
