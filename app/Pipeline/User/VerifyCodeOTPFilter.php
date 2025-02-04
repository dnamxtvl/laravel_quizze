<?php

namespace App\Pipeline\User;

use Illuminate\Database\Eloquent\Builder;

readonly class VerifyCodeOTPFilter
{
    public function __construct(
        private array $filters,
    ) {
    }

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['code'])) {
            $query->where('code', $this->filters['code']);
        }

        return $next($query);
    }
}
