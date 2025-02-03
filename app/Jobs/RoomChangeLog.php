<?php

namespace App\Jobs;

use App\Enums\Room\RoomStatusEnum;
use App\Models\Room;
use App\Repository\Interface\RoomRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RoomChangeLog implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Room $room,
        private readonly ?string $previousQuestionId = null,
        private readonly RoomStatusEnum $status
    ) {
    }

    /**
     * Execute the job.
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $roomRepository = app()->make(RoomRepositoryInterface::class);
        $roomRepository->saveRoomChangeLog(room: $this->room, previousQuestionId: $this->previousQuestionId, status: $this->status);
    }
}
