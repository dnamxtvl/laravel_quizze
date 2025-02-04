<?php

namespace App\Pipeline\User;

use Illuminate\Database\Eloquent\Builder;

readonly class RoleFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['role'])) {
            $query->where('type', $this->filters['role']);
        }

        return $next($query);
    }
}
