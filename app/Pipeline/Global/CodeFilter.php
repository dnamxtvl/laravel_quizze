<?php

namespace App\Pipeline\Global;

use Illuminate\Database\Eloquent\Builder;

readonly class CodeFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['code'])) {
            $query->where('code', $this->filters['code']);
        }

        return $next($query);
    }
}
