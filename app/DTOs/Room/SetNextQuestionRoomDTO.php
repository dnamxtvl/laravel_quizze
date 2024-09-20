<?php

namespace App\DTOs\Room;

use App\Enums\Room\RoomStatusEnum;
use Carbon\Carbon;

class SetNextQuestionRoomDTO
{
    public function __construct(
        private readonly string $currentQuestionId,
        private readonly Carbon $currentQuestionStartAt,
        private readonly Carbon $currentQuestionEndAt,
        private readonly RoomStatusEnum $status,
        private readonly ?Carbon $startAt = null,
        private ?Carbon $endAt = null,
    ) {}

    public function getStatus(): RoomStatusEnum
    {
        return $this->status;
    }

    public function getCurrentQuestionId(): string
    {
        return $this->currentQuestionId;
    }

    public function getCurrentQuestionStartAt(): Carbon
    {
        return $this->currentQuestionStartAt;
    }

    public function getCurrentQuestionEndAt(): Carbon
    {
        return $this->currentQuestionEndAt;
    }

    public function getStartAt(): ?Carbon
    {
        return $this->startAt;
    }

    public function getEndAt(): ?Carbon
    {
        return $this->endAt;
    }

    public function setEndAt(Carbon $endAt): void
    {
        $this->endAt = $endAt;
    }
}
