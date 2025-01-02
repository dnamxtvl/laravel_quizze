<?php

namespace App\Pipeline\User;

use Illuminate\Database\Eloquent\Builder;

readonly class IpFilter
{
    public function __construct(
        private array $filters,
    ) {
    }

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['ip'])) {
            $query->where('ip', $this->filters['ip']);
        }

        return $next($query);
    }
}
