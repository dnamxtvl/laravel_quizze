<?php

namespace App\Repository\Implement;

use App\DTOs\Gamer\SaveAnswerDTO;
use App\Enums\Room\RoomTypeEnum;
use App\Models\Answer;
use App\Models\GamerAnswer;
use App\Pipeline\Room\GamerIdFiler;
use App\Pipeline\Room\QuestionIdFilter;
use App\Pipeline\Room\RoomIdFilter;
use App\Repository\Interface\AnswerRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;

readonly class AnswerRepository implements AnswerRepositoryInterface
{
    public function __construct(
        private GamerAnswer $gamerAnswer,
        private Answer $answer,
    ) {}

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->gamerAnswer->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new GamerIdFiler(filters: $filters),
                new RoomIdFilter(filters: $filters),
                new QuestionIdFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function findById(string $answerId): ?Model
    {
        return $this->answer->query()->find(id: $answerId);
    }

    public function saveAnswer(SaveAnswerDTO $saveAnswer, bool $isUpdate = false): Model
    {
        $gamerAnswer = ! $isUpdate ? new GamerAnswer : $this->getQuery(filters: [
            'gamer_id' => $saveAnswer->getGamerId(),
            'question_id' => $saveAnswer->getQuestionId(),
        ])->first();
        $gamerAnswer->gamer_id = $saveAnswer->getGamerId();
        $gamerAnswer->question_id = $saveAnswer->getQuestionId();
        $gamerAnswer->answer_id = $saveAnswer->getAnswerId();
        $gamerAnswer->answer_in_time = $saveAnswer->getAnswerInTime();
        $gamerAnswer->score = $saveAnswer->getScore();
        $gamerAnswer->room_id = $saveAnswer->getRoomId();
        $gamerAnswer->type = $saveAnswer->getRoomType()->value;
        $gamerAnswer->save();

        return $gamerAnswer;
    }

    public function getScoreByAnswerIds(array $answerIds): array
    {
        return $this->answer->query()
            ->whereIn('id', $answerIds)
            ->pluck('is_correct', 'id')
            ->toArray();
    }

    public function updateResultExam(array $listQuestion, array $listAnswer, string $gamerId, string $roomId): void
    {
        $this->gamerAnswer->query()
            ->where('room_id', $roomId)
            ->where('gamer_id', $gamerId)
            ->delete();

        $answers = array_keys($listAnswer);
        $scores = array_values($listAnswer);
        $arrayResult = [];
        foreach ($listQuestion as $index => $questionId) {
            $arrayResult[] = [
                'question_id' => $questionId,
                'answer_id' => $answers[$index],
                'score' => $scores[$index],
                'type' => RoomTypeEnum::HOMEWORK->value,
                'gamer_id' => $gamerId,
                'room_id' => $roomId,
                'answer_in_time' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($arrayResult) > 0) {
            $this->gamerAnswer->query()->insert(
                values: $arrayResult,
            );
        }
    }

    public function getByQuestionId(string $questionId, string $roomId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->answer->query()
            ->where('question_id', $questionId)
            ->withCount(['gamerAnswers' => function ($query) use ($roomId) {
                $query->where('room_id', $roomId);
            }])
            ->get();
    }
}
