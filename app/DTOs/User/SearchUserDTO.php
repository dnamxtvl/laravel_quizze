<?php

namespace App\DTOs\User;

use App\Enums\Quiz\CreatedByEnum;
use App\Enums\User\UserRoleEnum;

readonly class SearchUserDTO
{
    public function __construct(
        private ?array $userIds,
        private array $createdAt = [],
        private ?bool $disabled,
        private ?UserRoleEnum $role,
    ) {}

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
