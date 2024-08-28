<?php

namespace App\Events;

use App\Repository\Interface\GamerRepositoryInterface;
use App\Repository\Interface\RoomRepositoryInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserJoinRoomEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $roomId,
        public readonly string $userId,
        public readonly string $username,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function broadcastWith(): array
    {
        $room = app()->make(abstract: RoomRepositoryInterface::class)->findById(roomId: $this->roomId);
        $gamerRepository = app()->make(abstract: GamerRepositoryInterface::class);
        $userIds = $room->gamerTokens->pluck('gamer_id')->toArray();

        return [
            'roomId' => $this->roomId,
            'gamers' => $gamerRepository->findByIds(ids: $userIds)->toArray(),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(name: 'user.join-room.'.$this->roomId),
        ];
    }
}
