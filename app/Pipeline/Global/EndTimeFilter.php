<?php

namespace App\Pipeline\Global;

use Illuminate\Database\Eloquent\Builder;

readonly class EndTimeFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['end_time'])) {
            $query->where('created_at', '<=', $this->filters['end_time']);
        }

        return $next($query);
    }
}
