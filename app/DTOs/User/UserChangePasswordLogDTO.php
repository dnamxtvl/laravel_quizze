<?php

namespace App\DTOs\User;

readonly class UserChangePasswordLogDTO
{
    public function __construct(
        private string $userId,
        private string $ip,
        private string $userAgent,
        private ?string $oldPassword,
        private string $newPassword,
        private string $changeBy,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function getChangeBy(): string
    {
        return $this->changeBy;
    }
}
