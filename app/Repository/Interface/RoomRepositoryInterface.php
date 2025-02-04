<?php

namespace App\Repository\Interface;

use App\DTOs\Room\CreateRoomParamsDTO;
use App\DTOs\Room\SetNextQuestionRoomDTO;
use App\Enums\Room\RoomStatusEnum;
use App\Models\Room;
use Carbon\Carbon;
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

    public function countRoom(): int;

    public function groupByYear(Carbon $startTime, Carbon $endTime): Collection;

    public function countByTime(Carbon $startTime, Carbon $endTime): int;

    public function saveRoomChangeLog(Room $room, RoomStatusEnum $status, ?string $previousQuestionId = null): void;
}
