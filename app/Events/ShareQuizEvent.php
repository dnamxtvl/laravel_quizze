<?php

namespace App\Events;

use App\Repository\Interface\GamerRepositoryInterface;
use App\Repository\Interface\RoomRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShareQuizEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $link,
        public readonly string $title,
        public readonly string $content,
        public readonly Carbon $createdAt,
        public readonly string $notifyId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(name: 'admin.share-quiz.'.$this->userId),
        ];
    }
}
