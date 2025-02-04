<?php

namespace App\DTOs\User;

use App\Enums\User\UserStatusEnum;

readonly class UserDisableLogDTO
{
    public function __construct(
        private string $disableBy,
        private string $userId,
        private UserStatusEnum $status,
    ) {}

    /**
     * @return string
     */
    public function getDisableBy(): string
    {
        return $this->disableBy;
    }

    public function getStatus(): UserStatusEnum
    {
        return $this->status;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function toArray(): array
    {
        return [
            'user_ids' => $this->userIds,
            'created_at_between' => $this->createdAt,
            'disabled' => $this->disabled,
            'role' => $this->role?->value,
        ];
    }
}
