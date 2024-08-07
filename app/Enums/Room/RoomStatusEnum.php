<?php

namespace App\Enums\Room;

enum RoomStatusEnum: int
{
    case PREPARE = 0;
    case HAPPENING = 1;
    case FINISHED = 2;
}
