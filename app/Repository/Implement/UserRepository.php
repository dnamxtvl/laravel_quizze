<?php

namespace App\Repository\Implement;

use App\Models\User;
use App\Repository\Interface\UserRepositoryInterface;

readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private User $user
    ) {}

    public function findByEmail(string $email): ?User
    {
        return $this->user->query()->where('email', $email)->first();
    }
}
