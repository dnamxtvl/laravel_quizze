<?php

namespace App\Enums\Auth;

enum TypeForgotPasswordLogEnum: int
{
    case USER_REQUEST_FORGOT_PASSWORD = 1;

    case PASSWORD_CHANGED = 2;
}
