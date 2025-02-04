<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class LoginWrongPasswordManyException extends TooManyRequestsHttpException
{
    public function __construct(string $message = 'Tài khoản đã nhập sai mật khẩu quá nhiều!Vui lòng thử lại sau 1h!', int $code = 0)
    {
        parent::__construct(message: $message, code: $code);
    }
}
