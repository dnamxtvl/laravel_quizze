<?php

namespace App\Services\Interface;

use App\DTOs\Auth\AdminLoginDTOs;
use App\DTOs\Auth\AdminLoginResponseDataDTO;

interface AuthServiceInterface
{
    public function login(AdminLoginDTOs $credentials): AdminLoginResponseDataDTO;

    public function logout(): void;
}
