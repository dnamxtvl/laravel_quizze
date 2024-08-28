<?php

namespace App\Pipeline\Room;

use Illuminate\Database\Eloquent\Builder;

readonly class QuestionIdFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['question_id'])) {
            $query->where('question_id', $this->filters['question_id']);
        }

        return $next($query);
    }
}
