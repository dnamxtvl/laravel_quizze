<?php

namespace App\Repository\Interface;

use App\DTOs\Gamer\SaveAnswerDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface AnswerRepositoryInterface
{
    public function findById(string $answerId): ?Model;

    public function saveAnswer(SaveAnswerDTO $saveAnswer, bool $isUpdate = false): Model;

    public function getQuery(array $columnSelects = [], array $filters = []): Builder;

    public function getScoreByAnswerIds(array $answerIds): array;

    public function updateResultExam(array $listQuestion, array $listAnswer, string $gamerId, string $roomId): void;

    public function getByQuestionId(string $questionId, string $roomId): Collection;

    public function countAnswerByTime(Carbon $startTime, Carbon $endTime): array;
}
