<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidOTPException extends BadRequestHttpException
{
    public function __construct(string $message = 'Mã xác thực không hợp lệ!', int $code = 0)
    {
        parent::__construct(message: $message, code: $code);
    }
}
