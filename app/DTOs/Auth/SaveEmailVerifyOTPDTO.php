<?php

namespace App\DTOs\Auth;

use Carbon\Carbon;

readonly class SaveEmailVerifyOTPDTO
{
    public function __construct(
        private string $code,
        private string $userId,
        private Carbon $expiredAt,
        private TypeCodeOTPEnum $type,
        private string $token
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getExpiredAt(): Carbon
    {
        return $this->expiredAt;
    }

    public function getType(): TypeCodeOTPEnum
    {
        return $this->type;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
