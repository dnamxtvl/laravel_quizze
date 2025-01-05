<?php

namespace App\Pipeline\Room;

use Illuminate\Database\Eloquent\Builder;

readonly class CodeQuizFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['code_quiz'])) {
            $query->whereHas('quizze', fn($q) => $q->where('code', $this->filters['code_quiz']));
        }

        return $next($query);
    }
}
