<?php

namespace App\Repository\Interface;

use App\DTOs\Room\CreateRoomParamsDTO;
use App\DTOs\Room\SetNextQuestionRoomDTO;
use App\Models\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoomRepositoryInterface
{
    public function createRoom(string $quizId, int $code, CreateRoomParamsDTO $createRoomParams): Model;

    public function findRoomByCode(int $code, array $filters = []): ?Model;

    public function findById(string $roomId): ?Model;

    public function updateRoomAfterNextQuestion(Room $room, SetNextQuestionRoomDTO $nextQuestionRoomDTO): Room;

    public function getListRoomByAdminId(string $userId, int $page, array $filters = []): LengthAwarePaginator;
}
