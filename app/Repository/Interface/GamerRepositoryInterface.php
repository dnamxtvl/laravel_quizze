<?php

namespace App\Repository\Interface;

use App\DTOs\User\UserDeviceInformationDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface GamerRepositoryInterface
{
    public function createGamer(UserDeviceInformationDTO $gamerInfo): Model;

    public function findById(string $gamerId): ?Model;

    public function findByIds(array $ids): Collection;
}
