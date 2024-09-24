<?php

namespace App\Repository\Implement;

use App\DTOs\Room\CreateRoomParamsDTO;
use App\DTOs\Room\SetNextQuestionRoomDTO;
use App\Enums\Room\RoomStatusEnum;
use App\Enums\Room\RoomTypeEnum;
use App\Models\Room;
use App\Pipeline\Global\QuizzIdFilter;
use App\Pipeline\Global\StatusFilter;
use App\Repository\Interface\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;

readonly class RoomRepository implements RoomRepositoryInterface
{
    public function __construct(
        private Room $room
    ) {}

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

    public function createRoom(string $quizId, int $code, CreateRoomParamsDTO $createRoomParams): Model
    {
        $room = new Room;
        $room->quizze_id = $quizId;
        $room->code = $code;
        $room->status = RoomStatusEnum::PREPARE->value;
        $room->type = $createRoomParams->getType()->value;
        if ($createRoomParams->getType() === RoomTypeEnum::HOMEWORK) {
            $room->started_at = $createRoomParams->getStartAt();
            $room->ended_at = $createRoomParams->getEndAt();
        }
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
        return $this->room->query()->with('gamers')->find(id: $roomId);
    }

    public function updateRoomAfterNextQuestion(Room $room, SetNextQuestionRoomDTO $nextQuestionRoomDTO): Room
    {
        if ($room->type != RoomTypeEnum::HOMEWORK->value) {
            $room->current_question_id = $nextQuestionRoomDTO->getCurrentQuestionId();
            $room->current_question_start_at = $nextQuestionRoomDTO->getCurrentQuestionStartAt();
            $room->current_question_end_at = $nextQuestionRoomDTO->getCurrentQuestionEndAt();
        }
        $room->status = $nextQuestionRoomDTO->getStatus()->value;
        if ($nextQuestionRoomDTO->getStartAt()) {
            $room->started_at = $nextQuestionRoomDTO->getStartAt();
        }
        if ($nextQuestionRoomDTO->getEndAt()) {
            $room->ended_at = $nextQuestionRoomDTO->getEndAt();
        }
        $room->save();

        return $room;
    }

    public function getListRoomByAdminId(string $userId): Collection
    {
        // TODO: Implement getListRoomByAdminId() method.
    }
}
