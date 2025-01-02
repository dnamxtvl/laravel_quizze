<?php

namespace App\Services\Interface;

use App\DTOs\User\SearchUserDTO;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function searchUser(SearchUserDTO $searchUser): LengthAwarePaginator;

    public function findUser(string $userId): User;

    public function deleteUser(string $userId): void;

    public function disableUser(string $userId): void;

    public function activeUser(string $userId): void;
}
