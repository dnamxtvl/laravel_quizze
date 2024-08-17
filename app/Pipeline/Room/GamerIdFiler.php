<?php

namespace App\Pipeline\Room;

use Illuminate\Database\Eloquent\Builder;

readonly class GamerIdFiler
{
    public function __construct(
        private array $filters,
    ) {
    }

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['gamer_id'])) {
            $query->where('gamer_id', $this->filters['gamer_id']);
        }

        return $next($query);
    }
}
