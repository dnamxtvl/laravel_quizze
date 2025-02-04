<?php

namespace App\Pipeline\Global;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

readonly class CreatedBySysFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        Log::info('Created By Sys Filter', $this->filters);
        if (isset($this->filters['created_by_sys'])) {
            $query->where('created_by_sys', $this->filters['created_by_sys']);
        }

        return $next($query);
    }
}
