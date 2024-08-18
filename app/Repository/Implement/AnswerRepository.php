<?php

namespace App\Repository\Implement;

use App\DTOs\Gamer\SaveAnswerDTO;
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
    ) {
    }

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

    public function saveAnswer(SaveAnswerDTO $saveAnswer): Model
    {
        $gamerAnswer = new GamerAnswer();
        $gamerAnswer->gamer_id = $saveAnswer->getGamerId();
        $gamerAnswer->question_id = $saveAnswer->getQuestionId();
        $gamerAnswer->answer_id = $saveAnswer->getAnswerId();
        $gamerAnswer->answer_in_time = $saveAnswer->getAnswerInTime();
        $gamerAnswer->score = $saveAnswer->getScore();
        $gamerAnswer->room_id = $saveAnswer->getRoomId();
        $gamerAnswer->save();

        return $gamerAnswer;
    }
}
