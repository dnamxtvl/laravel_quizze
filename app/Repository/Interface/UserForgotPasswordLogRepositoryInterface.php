<?php

namespace App\Repository\Interface;

use App\DTOs\Auth\UserForgotPasswordLogDTO;

interface UserForgotPasswordLogRepositoryInterface
{
    public function save(UserForgotPasswordLogDTO $forgotPasswordLog): void;
}
