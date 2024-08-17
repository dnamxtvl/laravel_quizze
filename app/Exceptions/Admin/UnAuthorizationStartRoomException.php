<?php

namespace App\Exceptions\Admin;

use Laravel\Horizon\Exceptions\ForbiddenException;
use Symfony\Component\HttpFoundation\Response;

class UnAuthorizationStartRoomException extends ForbiddenException
{
    public function __construct(string $message = 'Bạn không có quyền start màn chơi này!', int $code = 0)
    {
        parent::__construct(statusCode: Response::HTTP_FORBIDDEN, message: $message, code: $code);
    }
}
