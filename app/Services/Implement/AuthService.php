<?php

namespace App\Services\Implement;

use App\DTOs\Auth\AdminLoginDTOs;
use App\DTOs\Auth\AdminLoginResponseDataDTO;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\User\UserRoleEnum;
use App\Models\User;
use App\Services\Interface\AuthServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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
        ];

        if (!Auth::attempt(credentials: $adminCredentials)) {
            throw new BadRequestException(message: 'Sai email hoặc mật khẩu!', code: ExceptionCodeEnum::INVALID_CREDENTIALS->value);
        }

        $user = Auth::user();
        /** @var User $user */
        $tokenResult = $user->createToken(name: 'API Token');

        return new AdminLoginResponseDataDTO(
            user: $user,
            token: $tokenResult->accessToken,
            expiresAt: Carbon::parse($tokenResult->token->getAttribute(key: 'expires_at')),
        );
    }

    public function logout(): void
    {
    }
}
