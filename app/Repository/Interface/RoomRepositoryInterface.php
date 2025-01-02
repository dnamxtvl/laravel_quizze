<?php

namespace App\Repository\Interface;

use App\DTOs\Room\CreateRoomParamsDTO;
use App\DTOs\Room\SetNextQuestionRoomDTO;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoomRepositoryInterface
{
    public function createRoom(string $quizId, int $code, CreateRoomParamsDTO $createRoomParams): Model;

    public function findRoomByCode(int $code, array $filters = []): ?Model;

    public function findById(string $roomId): ?Model;

    public function updateRoomAfterNextQuestion(Room $room, SetNextQuestionRoomDTO $nextQuestionRoomDTO): Room;

    public function getListRoom(int $page, array $filters = []): LengthAwarePaginator;

    public function deleteRoom(Room $room): void;

    public function getListRoomRunning(string $quizId): Collection;
}
