<?php

namespace App\Pipeline\Quizzes;

use Illuminate\Database\Eloquent\Builder;

readonly class CategoryIdFilter
{
    public function __construct(
        private array $filters,
    ) {
    }

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $next($query);
    }
}
