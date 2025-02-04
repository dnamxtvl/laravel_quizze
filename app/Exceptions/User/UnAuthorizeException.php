<?php

namespace App\Exceptions\User;

use Laravel\Horizon\Exceptions\ForbiddenException;
use Symfony\Component\HttpFoundation\Response;

class UnAuthorizeException extends ForbiddenException
{
    public function __construct(string $message = 'Bạn không có quyền thực hiện hành động này!', int $code = 0)
    {
        parent::__construct(statusCode: Response::HTTP_FORBIDDEN, message: $message, code: $code);
    }
}
