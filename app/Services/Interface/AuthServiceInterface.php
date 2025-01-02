<?php

namespace App\Services\Interface;

use App\DTOs\Auth\AdminLoginDTOs;
use App\DTOs\Auth\AdminLoginResponseDataDTO;
use App\DTOs\Auth\UserDeviceInformationDTO;

interface AuthServiceInterface
{
    public function login(AdminLoginDTOs $credentials, UserDeviceInformationDTO $userDeviceInformation): AdminLoginResponseDataDTO;

    public function logout(): void;

    public function verifyOTPAfterLogin(string $code, string $email): AdminLoginResponseDataDTO;
}
