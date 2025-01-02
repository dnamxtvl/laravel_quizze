<?php

namespace App\Enums\User;

enum UserRoleEnum: int
{
    case ADMIN = 1;
    case USER = 2;
    case SYSTEM = 3;
}
