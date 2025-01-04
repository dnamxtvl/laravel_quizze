<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EmailAlreadyExistsException extends BadRequestHttpException
{
    public function __construct(string $message = 'Tài khoản được đăng ký trước đó!', int $code = 0)
    {
        parent::__construct(message: $message, code: $code);
    }
}
