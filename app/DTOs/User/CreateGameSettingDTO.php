<?php

namespace App\DTOs\User;

class CreateGameSettingDTO
{
    public function __construct(
        private readonly string $name,
        private readonly bool $isMeme
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIsMeme(): bool
    {
        return $this->isMeme;
    }
}
