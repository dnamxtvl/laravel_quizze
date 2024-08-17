<?php

namespace App\Repository\Interface;

use App\DTOs\Room\SetNextQuestionRoomDTO;
use App\Models\Room;
use Illuminate\Database\Eloquent\Model;

interface RoomRepositoryInterface
{
    public function createRoom(string $quizId, int $code): Model;

    public function findRoomByCode(int $code, array $filters = []): ?Model;

    public function findById(string $roomId): ?Model;

    public function updateRoomAfterNextQuestion(Room $room, SetNextQuestionRoomDTO $nextQuestionRoomDTO): Room;
}
