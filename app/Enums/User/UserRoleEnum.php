<?php

namespace App\Enums\User;

enum UserRoleEnum: int
{
    case ADMIN = 1;
    case USER = 2;
    case SYSTEM = 3;

    public static function toArrayValue(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
