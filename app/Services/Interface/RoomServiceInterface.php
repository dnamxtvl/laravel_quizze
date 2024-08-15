<?php

namespace App\Services\Interface;

use Illuminate\Database\Eloquent\Model;

interface RoomServiceInterface
{
    public function createRoom(string $quizId): Model;

    public function checkValidRoom(string $roomId): Model;
}
