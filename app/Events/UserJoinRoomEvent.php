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
use App\Models\Gamer;

class UserJoinRoomEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $roomId,
        public readonly Gamer $gamer,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function broadcastWith(): array
    {
        return [
            'roomId' => $this->roomId,
            'gamer' => $this->gamer,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(name: 'user.join-room.'.$this->roomId),
        ];
    }
}
