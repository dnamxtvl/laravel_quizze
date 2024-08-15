<?php

namespace App\Repository\Interface;

use Illuminate\Database\Eloquent\Model;

interface RoomRepositoryInterface
{
    public function createRoom(string $quizId, int $code): Model;

    public function findRoomByCode(int $code, array $filters = []): ?Model;

    public function findById(string $roomId): ?Model;
}
