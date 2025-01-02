<?php

namespace App\Repository\Interface;

use App\Models\BlockUserLoginTemporary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface BlockUserLoginTemporaryRepositoryInterface
{
    public function getQuery(array $columnSelects = [], array $filters = []): Builder;

    public function save(string $ip, string $userId, Carbon $expiredAt): void;

    public function findByUserAndIp(string $ip, string $userId): ?BlockUserLoginTemporary;
}
