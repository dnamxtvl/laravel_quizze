<?php

namespace App\Pipeline\UserShareQuestion;

use Illuminate\Database\Eloquent\Builder;

readonly class UserShareIdFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['user_share_id'])) {
            $query->where('user_share_id', $this->filters['user_share_id']);
        }

        return $next($query);
    }
}
