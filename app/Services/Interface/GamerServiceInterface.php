<?php

namespace App\Services\Interface;

use App\DTOs\Gamer\CreateGameSettingDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface GamerServiceInterface
{
    public function createGameSetting(string $token, string $gamerId, CreateGameSettingDTO $createGameSettingDTO): Model;

    public function submitAnswer(string $token, int $answerId): Model;

    public function userOutGame(string $token): void;

    public function submitHomework(string $token, array $listQuestion, array $listAnswer, bool $autoSubmit = false): void;

    public function countGamer(): int;

    public function groupByYear(Carbon $startTime, Carbon $endTime): Collection;

    public function countByTime(Carbon $startTime, Carbon $endTime): int;
}
