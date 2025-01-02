<?php

namespace App\Repository\Interface;

use App\DTOs\User\SearchUserDTO;
use App\DTOs\User\UserDisableLogDTO;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function searchUser(SearchUserDTO $searchUser): LengthAwarePaginator;

    public function findById(string $userId): ?User;

    public function delete(User $user): void;

    public function disable(User $user): void;

    public function saveDisableLog(UserDisableLogDTO $userDisableLog): void;

    public function enable(User $user): void;

    public function verifyEmail(User $user): void;
}
