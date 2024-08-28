<?php

namespace App\Repository\Implement;

use App\DTOs\User\UserDeviceInformationDTO;
use App\Models\Gamer;
use App\Repository\Interface\GamerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

readonly class GamerRepository implements GamerRepositoryInterface
{
    public function __construct(
        private Gamer $gamer
    ) {}

    public function createGamer(UserDeviceInformationDTO $gamerInfo): Model
    {
        $gamer = new Gamer;
        $gamer->longitude = $gamerInfo->getLongitude();
        $gamer->latitude = $gamerInfo->getLatitude();
        $gamer->ip_address = $gamerInfo->getIp();
        $gamer->country_name = $gamerInfo->getCountry();
        $gamer->city_name = $gamerInfo->getCity();
        $gamer->user_agent = $gamerInfo->getDevice();
        $gamer->display_meme = false;
        $gamer->save();

        return $gamer;
    }

    public function findById(string $gamerId): ?Model
    {
        return $this->gamer->query()->find(id: $gamerId);
    }

    public function findByIds(array $ids): Collection
    {
        return $this->gamer->query()->whereIn('id', $ids)->get();
    }
}
