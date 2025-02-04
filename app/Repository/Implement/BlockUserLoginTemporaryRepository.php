<?php

namespace App\Repository\Implement;

use App\Models\BlockUserLoginTemporary;
use App\Pipeline\Global\UserIdFilter;
use App\Repository\Interface\BlockUserLoginTemporaryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

readonly class BlockUserLoginTemporaryRepository implements BlockUserLoginTemporaryRepositoryInterface
{
    public function __construct(
        private BlockUserLoginTemporary $blockUserLoginTemporary
    ) {
    }

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->blockUserLoginTemporary->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(Pipeline::class)
            ->send($query)
            ->through([
                new UserIdFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function save(string $ip, string $userId, Carbon $expiredAt): void
    {
        $blockUserLoginTemporary = new BlockUserLoginTemporary();
        $blockUserLoginTemporary->ip = $ip;
        $blockUserLoginTemporary->user_id = $userId;
        $blockUserLoginTemporary->expired_at = $expiredAt;

        $blockUserLoginTemporary->save();
    }

    public function findByUserAndIp(string $ip, string $userId): ?BlockUserLoginTemporary
    {
        return $this->blockUserLoginTemporary->query()
            ->where('ip', $ip)
            ->where('user_id', $userId)
            ->first();
    }
}
