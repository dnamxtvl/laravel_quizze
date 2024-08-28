<?php

namespace App\DTOs\Room;

use App\Models\Gamer;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

readonly class QuestionsOfRoomResponseDTO
{
    public function __construct(
        private Room $room,
        private Collection $questions,
        private Gamer $gamer,
        private int $timeRemaining,
    ) {}

    public function toArray(): array
    {
        return [
            'room' => $this->room,
            'questions' => $this->questions,
            'gamer' => $this->gamer,
            'time_remaining' => $this->timeRemaining,
        ];
    }
}
