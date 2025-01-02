<?php

namespace App\Repository\Implement;

use App\DTOs\Auth\UserForgotPasswordLogDTO;
use App\Models\UserForgotPasswordLog;
use App\Repository\Interface\UserForgotPasswordLogRepositoryInterface;

class UserForgotPasswordLogRepository implements UserForgotPasswordLogRepositoryInterface
{
    public function save(UserForgotPasswordLogDTO $forgotPasswordLog): void
    {
        $userForgotPasswordLog = new UserForgotPasswordLog();

        $userForgotPasswordLog->user_id = $forgotPasswordLog->getUserId();
        $userForgotPasswordLog->ip = $forgotPasswordLog->getIp();
        $userForgotPasswordLog->device = $forgotPasswordLog->getDevice();
        $userForgotPasswordLog->type = $forgotPasswordLog->getType()->value;

        $userForgotPasswordLog->save();
    }
}
