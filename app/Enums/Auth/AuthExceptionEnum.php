<?php

namespace App\Enums\Auth;

enum AuthExceptionEnum: int
{
    case INVALID_CODE = 8445338;

    case OTP_EXPIRED = 8422338;

    case EMAIL_VERIFIED = 8499338;

    case ACCOUNT_CLOSED = 3463368;

    case EMAIL_NOT_VERIFY = 3453628;

    case OTP_NOT_FOUND = 4546464;

    case LOGIN_WRONG_PASSWORD_MANY = 4546964;

    case EMAIL_ALREADY_EXISTS = 4546468;
}
