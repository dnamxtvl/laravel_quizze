<?php

namespace App\Exceptions\Quiz;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnAuthorizeRejectQuizException extends HttpException
{
    public function __construct(string $message = 'Bạn đã nhận bộ câu hỏi này trước đó!', int $code = 0)
    {
        parent::__construct(statusCode: Response::HTTP_FORBIDDEN, message: $message, code: $code);
    }
}
