<?php

namespace App\Repository\Implement;

use App\DTOs\Auth\UserLoginHistoryDTO;
use App\Models\UserLoginHistory;
use App\Pipeline\Global\TypeFilter;
use App\Pipeline\Global\UserIdFilter;
use App\Pipeline\User\IpFilter;
use App\Repository\Interface\UserLoginHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

readonly class UserLoginHistoryRepository implements UserLoginHistoryRepositoryInterface
{
    public function __construct(
        private UserLoginHistory $userLoginHistory
    ) {
    }

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->userLoginHistory->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(Pipeline::class)
            ->send($query)
            ->through([
                new UserIdFilter(filters: $filters),
                new TypeFilter(filters: $filters),
                new IpFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function save(UserLoginHistoryDTO $userLoginHistoryDTO): void
    {
        $userLoginHistory = new UserLoginHistory();

        $userLoginHistory->ip = $userLoginHistoryDTO->getIp();
        $userLoginHistory->user_id = $userLoginHistoryDTO->getUserId();
        $userLoginHistory->device = $userLoginHistoryDTO->getDevice();
        $userLoginHistory->type = $userLoginHistoryDTO->getType()->value;
        $userLoginHistory->save();
    }
}
