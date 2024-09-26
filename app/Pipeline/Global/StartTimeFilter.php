<?php

namespace App\Pipeline\Global;

use Illuminate\Database\Eloquent\Builder;

readonly class StartTimeFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['start_time'])) {
            $query->where('created_at', '>=', $this->filters['start_time']);
        }

        return $next($query);
    }
}
