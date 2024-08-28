<?php

namespace App\Pipeline\Room;

use Illuminate\Database\Eloquent\Builder;

readonly class RoomIdFilter
{
    public function __construct(
        private array $filters,
    ) {}

    public function handle(Builder $query, $next)
    {
        if (isset($this->filters['room_id'])) {
            $query->where('room_id', $this->filters['room_id']);
        }

        return $next($query);
    }
}
