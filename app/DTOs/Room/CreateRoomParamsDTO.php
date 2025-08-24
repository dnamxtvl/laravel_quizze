<?php

namespace App\DTOs\Room;

use App\Enums\Room\RoomTypeEnum;
use Carbon\Carbon;

class CreateRoomParamsDTO
{
    public function __construct(
        private readonly RoomTypeEnum $type,
        private readonly Carbon $startAt,
        private readonly Carbon $endAt,
        private ?array $questionIds = [],
        private ?array $settings = []
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

    public function getQuestionIds(): ?array
    {
        return $this->questionIds;
    }

    public function setQuestionIds(array $questionIds): void
    {
        $this->questionIds = $questionIds;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }
}
