<?php

namespace App\Pipeline\Global;

use Illuminate\Database\Eloquent\Builder;

readonly class CreatedAtBetweenFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (!empty($this->filters['created_at_between'])) {
            $query->whereBetween('created_at', $this->filters['created_at_between']);
        }

        return $next($query);
    }
}
