<?php

namespace App\DTOs\User;

use Carbon\Carbon;

readonly class VerifyCodeResponseDTO
{
    public function __construct(
        private string $gamerId,
        private string $token,
        private Carbon $expiredAt,
    ) {}

    public function toArray(): array
    {
        return [
            'gamer_id' => $this->gamerId,
            'token' => $this->token,
            'expired_at' => $this->expiredAt->toDateTimeString(),
        ];
    }
}
