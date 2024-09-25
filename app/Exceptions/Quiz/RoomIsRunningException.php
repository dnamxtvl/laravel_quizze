<?php

namespace App\Exceptions\Quiz;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoomIsRunningException extends HttpException
{
    public function __construct(string $message = 'Bạn không có quyền start màn chơi này!', int $code = 0)
    {
        parent::__construct(statusCode: Response::HTTP_FORBIDDEN, message: $message, code: $code);
    }
}
