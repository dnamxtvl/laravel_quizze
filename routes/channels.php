<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel(channel: 'channel_for_everyone', callback: function ($user) {
    return $user->id != 9;
});

Broadcast::channel('chat', function () {
    Log::info(message: 'ahihi test');
    return "ahihi";
});
