<?php

namespace App\Enums\Auth;

enum TypeUserHistoryLoginEnum: int
{
    case LOGIN_SUCCESS_NEW_IP = 1;

    case WRONG_PASSWORD = 2;
}
