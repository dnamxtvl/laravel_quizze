<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OTPExpiredException extends BadRequestHttpException
{
    public function __construct(string $message = 'Mã xác thực đã hết hạn!', int $code = 0)
    {
        parent::__construct(message: $message, code: $code);
    }
}
