<?php

namespace App\Pipeline\User;

use Illuminate\Database\Eloquent\Builder;

readonly class StatusFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['disabled'])) {
            $query->where('disabled', $this->filters['disabled']);
        }

        return $next($query);
    }
}
