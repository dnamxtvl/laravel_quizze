<?php

namespace App\Pipeline\Global;

use Illuminate\Database\Eloquent\Builder;

readonly class UserIdsFiler
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (!empty($this->filters['user_ids'])) {
            $query->whereIn('user_id', $this->filters['user_ids']);
        }

        return $next($query);
    }
}
