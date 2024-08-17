<?php

namespace App\DTOs\Room;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

readonly class CheckValidRoomResponseDTO
{
    public function __construct(
        public Room       $room,
        public Collection $questions,
    ) {
    }

    public function toArray(): array
    {
        return [
            'room' => $this->room,
            'questions' => $this->questions,
        ];
    }
}
