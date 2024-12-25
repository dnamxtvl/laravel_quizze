<?php

namespace App\DTOs\Room;

use App\Models\Question;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

readonly class CheckValidRoomResponseDTO
{
    public function __construct(
        private Room $room,
        private Collection $questions,
        private Collection $gamers,
        private int $timeRemaining,
        private Collection $listCurrentAnswers,
    ) {}

    public function toArray(): array
    {
        return [
            'room' => $this->room,
            'questions' => $this->questions,
            'gamers' => $this->gamers,
            'time_remaining' => $this->timeRemaining,
            'list_current_answers' => $this->listCurrentAnswers,
        ];
    }
}
