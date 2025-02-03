<?php

namespace App\Repository\Interface;

use App\DTOs\Gamer\UserDeviceInformationDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface GamerRepositoryInterface
{
    public function createGamer(UserDeviceInformationDTO $gamerInfo): Model;

    public function findById(string $gamerId): ?Model;

    public function findByIds(array $ids): Collection;

    public function countAll(): int;

    public function groupByYear(Carbon $startTime, Carbon $endTime): Collection;

    public function countByTime(Carbon $startTime, Carbon $endTime): int;
}
