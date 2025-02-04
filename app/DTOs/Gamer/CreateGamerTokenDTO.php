<?php

namespace App\DTOs\Gamer;

readonly class CreateGamerTokenDTO
{
    public function __construct(
        private string $gamerId,
        private string $token,
        private string $roomId,
        private string $expiredAt,
    ) {}

    public function getGamerId(): string
    {
        return $this->gamerId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getRoomId(): string
    {
        return $this->roomId;
    }

    public function getExpiredAt(): string
    {
        return $this->expiredAt;
    }
}
