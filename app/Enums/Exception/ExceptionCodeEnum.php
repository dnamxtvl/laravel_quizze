<?php

namespace App\Enums\Exception;

enum ExceptionCodeEnum: int
{
    case INVALID_CREDENTIALS = 1001872;
    case UNVERIFIED_ACCOUNT = 1001873;
    case ROOM_CANCELLED = 1001874;
}
