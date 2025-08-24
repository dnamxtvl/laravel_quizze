<?php

namespace App\Pipeline\Global;

use Illuminate\Database\Eloquent\Builder;

readonly class CreatedBySysFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['created_by_sys'])) {
            $query->where('created_by_sys', $this->filters['created_by_sys']);
        }

        return $next($query);
    }
}
