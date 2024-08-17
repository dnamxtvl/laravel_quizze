<?php

namespace App\Repository\Implement;

use App\Models\Question;
use App\Pipeline\Global\QuizzIdFilter;
use App\Repository\Interface\QuestionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pipeline\Pipeline;

readonly class QuestionRepository implements QuestionRepositoryInterface
{
    public function __construct(
        private Question $question
    ) {
    }

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->question->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new QuizzIdFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function listQuestion(array $columnSelects = [], array $filters = []): Collection
    {
        return $this->getQuery(filters: $filters)->with(relations: 'answers')->get();
    }

    public function findNextQuestion(string $quzId, string $questionId): ?Question
    {
        return $this->question->query()
            ->where('id', '>', $questionId)
            ->orderBy('id')
            ->first();
    }
}
