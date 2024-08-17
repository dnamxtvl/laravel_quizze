<?php

namespace App\Enums\Room;

enum RoomStatusEnum: int
{
    case PREPARE = 0;
    case HAPPENING = 1;
    case FINISHED = 2;
    case CANCELLED = 3;
    case PENDING = 4;
    case PREPARE_FINISH = 5;
}
