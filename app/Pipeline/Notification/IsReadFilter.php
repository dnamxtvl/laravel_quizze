<?php

namespace App\Pipeline\Notification;

use Illuminate\Database\Eloquent\Builder;

readonly class IsReadFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['is_read'])) {
            $query->where('is_read', $this->filters['is_read']);
        }

        return $next($query);
    }
}
