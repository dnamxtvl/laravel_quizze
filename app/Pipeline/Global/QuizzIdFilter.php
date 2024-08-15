<?php

namespace App\Pipeline\Global;

use Illuminate\Database\Eloquent\Builder;

readonly class QuizzIdFilter
{
    public function __construct(
        private array $filters,
    ) {
    }

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['quizze_id'])) {
            $query->where('quizze_id', $this->filters['quizze_id']);
        }

        return $next($query);
    }
}
