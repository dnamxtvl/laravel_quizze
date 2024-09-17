<?php

namespace App\DTOs\Room;

use App\Enums\Room\RoomTypeEnum;
use Carbon\Carbon;

readonly class CreateRoomParamsDTO
{
    public function __construct(
        private RoomTypeEnum $type,
        private Carbon $startAt,
        private Carbon $endAt,
    ) {}

    public function getType(): RoomTypeEnum
    {
        return $this->type;
    }

    public function getStartAt(): Carbon
    {
        return $this->startAt;
    }

    public function getEndAt(): Carbon
    {
        return $this->endAt;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->tupe,
            'start_at' => $this->startAt,
            'end_at' => $this->endAt,
        ];
    }
}
