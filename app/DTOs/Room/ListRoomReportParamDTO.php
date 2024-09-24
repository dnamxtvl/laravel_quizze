<?php

namespace App\DTOs\Room;

use App\Enums\Room\RoomStatusEnum;
use App\Enums\Room\RoomTypeEnum;
use Carbon\Carbon;

readonly class ListRoomReportParamDTO
{
    public function __construct(
        private ?RoomTypeEnum $type = null,
        private ?RoomStatusEnum $status = null,
        private ?int $code = null,
        private ?Carbon $startTime = null,
        private ?Carbon $endTime = null,
        private ?int $page = null
    ) {}

    public function getType(): ?RoomTypeEnum
    {
        return $this->type;
    }

    public function getStatus(): ?RoomStatusEnum
    {
        return $this->status;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getStartTime(): ?Carbon
    {
        return $this->startTime;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getEndTime(): ?Carbon
    {
        return $this->endTime;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type?->value,
            'status' => $this->status?->value,
            'code' => $this->code,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
        ];
    }
}
