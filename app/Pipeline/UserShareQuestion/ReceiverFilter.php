<?php

namespace App\Pipeline\UserShareQuestion;

use Illuminate\Database\Eloquent\Builder;

readonly class ReceiverFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['receiver_id'])) {
            $query->where('receiver_id', $this->filters['receiver_id']);
        }

        return $next($query);
    }
}
