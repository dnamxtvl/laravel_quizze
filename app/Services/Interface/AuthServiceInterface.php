<?php

namespace App\Services\Interface;

use App\DTOs\Auth\AdminLoginDTOs;
use App\DTOs\Auth\AdminLoginResponseDataDTO;
use App\DTOs\Auth\RegisterParamsDTO;
use App\DTOs\Auth\UserDeviceInformationDTO;

interface AuthServiceInterface
{
    public function login(AdminLoginDTOs $credentials, UserDeviceInformationDTO $userDeviceInformation): AdminLoginResponseDataDTO;

    public function logout(): void;

    public function verifyOTPAfterLogin(string $code, string $email): AdminLoginResponseDataDTO;

    public function register(RegisterParamsDTO $registerParams): array;

    public function verifyOTPAfterRegister(string $otpId, string $token, string $code): AdminLoginResponseDataDTO;

    public function resendVerifyEmail(string $otpId): void;

    public function forgotPassword(string $email): void;

    public function resetPassword(string $userId, string $token, string $password): void;
}
