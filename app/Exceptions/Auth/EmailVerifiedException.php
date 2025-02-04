<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EmailVerifiedException extends BadRequestHttpException
{
    public function __construct(string $message = 'Email đã được xác thực trước đó!', int $code = 0)
    {
        parent::__construct(message: $message, code: $code);
    }
}
