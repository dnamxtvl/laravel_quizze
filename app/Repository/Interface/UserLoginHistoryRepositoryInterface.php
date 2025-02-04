<?php

namespace App\Repository\Interface;

use App\DTOs\Auth\UserLoginHistoryDTO;
use Illuminate\Database\Eloquent\Builder;

interface UserLoginHistoryRepositoryInterface
{
    public function getQuery(array $columnSelects = [], array $filters = []): Builder;

    public function save(UserLoginHistoryDTO $userLoginHistoryDTO): void;
}
