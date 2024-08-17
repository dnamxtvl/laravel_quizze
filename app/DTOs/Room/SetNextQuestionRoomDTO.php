<?php

namespace App\DTOs\Room;

use App\Enums\Room\RoomStatusEnum;
use Carbon\Carbon;

readonly class SetNextQuestionRoomDTO
{
    public function __construct(
        private string  $currentQuestionId,
        private Carbon  $currentQuestionStartAt,
        private Carbon  $currentQuestionEndAt,
        private RoomStatusEnum $status,
        private ?Carbon $startAt = null,
        private ?Carbon $endAt = null,
    ) {
    }

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
}
