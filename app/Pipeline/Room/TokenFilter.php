<?php

namespace App\Pipeline\Room;

use Illuminate\Database\Eloquent\Builder;

readonly class TokenFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['token'])) {
            $query->where('token', $this->filters['token']);
        }

        return $next($query);
    }
}
