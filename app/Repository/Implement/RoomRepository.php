<?php

namespace App\Repository\Implement;

use App\Enums\Room\RoomStatusEnum;
use App\Models\Room;
use App\Pipeline\Global\QuizzIdFilter;
use App\Pipeline\Global\StatusFilter;
use App\Repository\Interface\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;

readonly class RoomRepository implements RoomRepositoryInterface
{
    public function __construct(
        private Room $room
    ) {
    }

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->room->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new QuizzIdFilter(filters: $filters),
                new StatusFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function createRoom(string $quizId, int $code): Model
    {
        $room = new Room();
        $room->quizze_id = $quizId;
        $room->code = $code;
        $room->status = RoomStatusEnum::PREPARE->value;
        $room->save();

        return $room;
    }

    public function findRoomByCode(int $code, array $filters = []): ?Model
    {
        return $this->getQuery(filters: $filters)
            ->where('code', $code)
            ->first();
    }

    public function findById(string $roomId): ?Model
    {
        return $this->room->query()->find(id: $roomId);
    }
}
