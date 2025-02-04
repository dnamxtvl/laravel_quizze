<?php

namespace App\Services\Interface;

use App\DTOs\Auth\RegisterParamsDTO;
use App\DTOs\User\SearchUserDTO;
use App\DTOs\User\UpdateProfileDTO;
use App\DTOs\User\UserChangePasswordLogDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function searchUser(SearchUserDTO $searchUser): LengthAwarePaginator;

    public function findUser(string $userId): User;

    public function deleteUser(string $userId): void;

    public function disableUser(string $userId): void;

    public function activeUser(string $userId): void;

    public function searchByElk(string $keyword): Collection;

    public function changePassword(UserChangePasswordLogDTO $userChangePasswordLog, string $userId): void;

    public function updateProfile(UpdateProfileDTO $updateProfile): void;

    public function getLatestUser(): User;

    public function countCustomer(): int;

    public function createUser(RegisterParamsDTO $registerParams): void;
}
