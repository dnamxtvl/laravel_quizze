<?php

namespace App\Services\Interface;

use App\DTOs\User\CreateGameSettingDTO;
use Illuminate\Database\Eloquent\Model;

interface GamerServiceInterface
{
    public function createGameSetting(string $token, string $gamerId, CreateGameSettingDTO $createGameSettingDTO): Model;

    public function submitAnswer(string $token, int $answerId): Model;

    public function userOutGame(string $token): void;

    public function submitHomework(string $token, array $listQuestion, array $listAnswer, bool $autoSubmit = false): void;
}
