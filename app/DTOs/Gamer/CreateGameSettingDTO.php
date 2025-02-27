<?php

namespace App\DTOs\Gamer;

readonly class CreateGameSettingDTO
{
    public function __construct(
        private string $name,
        private bool $isMeme
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getIsMeme(): bool
    {
        return $this->isMeme;
    }
}
