<?php

namespace App\Services\Implement;

use App\DTOs\Auth\AdminLoginDTOs;
use App\DTOs\Auth\AdminLoginResponseDataDTO;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\User\UserRoleEnum;
use App\Exceptions\User\EmailNotVerifiedException;
use App\Models\User;
use App\Services\Interface\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthService implements AuthServiceInterface
{
    public function __construct()
    {
    }

    public function login(AdminLoginDTOs $credentials): AdminLoginResponseDataDTO
    {
        $adminCredentials = [
            'email' => $credentials->getEmail(),
            'password' => $credentials->getPassword(),
            'role' => UserRoleEnum::ADMIN->value,
        ];

        if (!Auth::attempt(credentials: $adminCredentials)) {
            throw new BadRequestHttpException(message: 'Sai email hoặc mật khẩu!', code: ExceptionCodeEnum::INVALID_CREDENTIALS->value);
        }

        $user = Auth::user();
        /** @var User $user */
        if (is_null($user->email_verified_at)) {
            throw new EmailNotVerifiedException(code: ExceptionCodeEnum::UNVERIFIED_ACCOUNT->value);
        }
        $tokenResult = $user->createToken('authToken')->plainTextToken;

        return new AdminLoginResponseDataDTO(
            user: $user,
            token: $tokenResult,
            expiresAt: now()->addMinutes(config(key:'sanctum.expiration')),
        );
    }

    public function logout(): void
    {
        $user = Auth::user();
        /** @var User $user */
        $user->tokens()->delete();
    }
}
