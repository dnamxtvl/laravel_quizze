<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::routes(['middleware' => 'auth:sanctum']);

Broadcast::channel('user.join-room.{id}', function ($user, $id) {
    $room = Room::query()->find(id: $id);
    if (is_null($room) || is_null($room->quizze)) {
        return false;
    }

    return $user->id == $room->quizze->user_id;
});

Broadcast::channel(channel: 'channel_for_everyone', callback: function ($user) {
    return $user->id != 9;
});

Broadcast::channel('chat', function () {
    Log::info(message: 'ahihi test');
    return "ahihi";
});
