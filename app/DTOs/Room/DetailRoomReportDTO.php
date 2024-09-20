<?php

namespace App\DTOs\Room;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

readonly class DetailRoomReportDTO
{
    public function __construct(
        private Room $room,
        private Collection $questions,
        private Collection $gamers,
    ) {}

    public function toArray(): array
    {
        return [
            'room' => $this->room,
            'questions' => $this->questions,
            'gamers' => $this->gamers,
        ];
    }
}
