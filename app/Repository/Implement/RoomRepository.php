<?php

namespace App\Repository\Implement;

use App\DTOs\Room\CreateRoomParamsDTO;
use App\DTOs\Room\SetNextQuestionRoomDTO;
use App\Enums\Room\RoomStatusEnum;
use App\Enums\Room\RoomTypeEnum;
use App\Models\Room;
use App\Models\RoomChangeLog;
use App\Pipeline\Global\CodeFilter;
use App\Pipeline\Room\CodeQuizFilter;
use App\Pipeline\Global\EndTimeFilter;
use App\Pipeline\Global\QuizzIdFilter;
use App\Pipeline\Global\StartTimeFilter;
use App\Pipeline\Global\StatusFilter;
use App\Pipeline\Global\TypeFilter;
use App\Pipeline\Global\UserIdFilter;
use App\Repository\Interface\RoomRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;

readonly class RoomRepository implements RoomRepositoryInterface
{
    public function __construct(
        private Room $room,
        private RoomChangeLog $roomChangeLog,
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
                new UserIdFilter(filters: $filters),
                new CodeFilter(filters: $filters),
                new TypeFilter(filters: $filters),
                new StartTimeFilter(filters: $filters),
                new EndTimeFilter(filters: $filters),
                new CodeQuizFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function createRoom(string $quizId, int $code, CreateRoomParamsDTO $createRoomParams): Model
    {
        $room = new Room;
        $room->quizze_id = $quizId;
        $room->user_id = Auth::id();
        $room->code = $code;
        $room->status = RoomStatusEnum::PREPARE->value;
        $room->type = $createRoomParams->getType()->value;
        $room->list_question = json_encode($createRoomParams->getQuestionIds());
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
            $room->current_question_end_at = $nextQuestionRoomDTO->getCurrentQuestionEndAt()->format('Y-m-d H:i:s.u');
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

    public function getListRoom(int $page, array $filters = []): LengthAwarePaginator
    {
        return $this->getQuery(filters: $filters)
            ->withCount(['gamers', 'gamerAnswers', 'gamerAnswers as total_correct' => function ($query) {
                $query->where('score', '>', 0);
            }])
            ->with('quizze:id,title')
            ->whereHas('quizze')
            ->orderBy(column: 'created_at', direction: 'desc')
            ->paginate(perPage: config('app.room_report.limit_pagination'), page: $page);
    }

    public function deleteRoom(Room $room): void
    {
        $room->delete();
        $room->gamerTokens()->delete();
        $room->gamerAnswers()->delete();
        $room->gamers()->delete();
    }

    public function getListRoomRunning(string $quizId): Collection
    {
        return $this->room->query()
            ->where('quizze_id', $quizId)
            ->whereNotIn('status', [RoomStatusEnum::FINISHED->value, RoomStatusEnum::CANCELLED->value])
            ->get();
    }

    public function countRoom(): int
    {
        return $this->room->query()->count();
    }

    public function groupByYear(Carbon $startTime, Carbon $endTime): Collection
    {
        return $this->room->query()
            ->whereBetween('created_at', [$endTime, $startTime])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_year, COUNT(*) as total")
            ->groupBy('month_year')
            ->orderBy('month_year')
            ->get();
    }

    public function countByTime(Carbon $startTime, Carbon $endTime): int
    {
        return $this->room->query()->whereBetween('created_at', [$endTime, $startTime])->count();
    }

    public function saveRoomChangeLog(Room $room, RoomStatusEnum $status, ?string $previousQuestionId = null): void
    {
        $log = new RoomChangeLog();
        $log->room_id = $room->id;
        $log->previous_question_id = $room->$previousQuestionId;
        $log->current_question_id = $room->current_question_id;
        $log->status = $status->value;

        $log->save();
    }
}
