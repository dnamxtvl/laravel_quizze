<?php

namespace App\Enums\User;

enum ActionEnum: int
{
    case EDIT = 2;
    case VIEW = 1;
    case NONE = 0;
}
