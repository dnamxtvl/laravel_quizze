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
        private ?string $backgroundName = null,
        private ?string $music = null,
        private ?string $musicName = null,
        private readonly ?string $oldBackground = null,
        private readonly ?string $oldBackgroundName = null,
        private readonly ?string $oldMusic = null,
        private readonly ?string $oldMusicName = null,
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

    public function getBackgroundName(): ?string
    {
        return $this->backgroundName;
    }

    public function getMusicName(): ?string
    {
        return $this->musicName;
    }

    public function setBackgroundName(string $backgroundName): void
    {
        $this->backgroundName = $backgroundName;
    }

    public function setMusicName(string $musicName): void
    {
        $this->musicName = $musicName;
    }

    public function getOldBackground(): ?string
    {
        return $this->oldBackground;
    }

    public function getOldBackgroundName(): ?string
    {
        return $this->oldBackgroundName;
    }

    public function getOldMusic(): ?string
    {
        return $this->oldMusic;
    }

    public function getOldMusicName(): ?string
    {
        return $this->oldMusicName;
    }
}
