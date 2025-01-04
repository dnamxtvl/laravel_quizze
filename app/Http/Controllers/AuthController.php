<?php

namespace App\Http\Controllers;

use App\DTOs\Auth\AdminLoginDTOs;
use App\DTOs\Auth\RegisterParamsDTO;
use App\DTOs\Auth\UserDeviceInformationDTO;
use App\Enums\User\UserRoleEnum;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyEmailOTPAfterLoginRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Services\Interface\AuthServiceInterface;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    public function login(AdminLoginRequest $request): JsonResponse
    {
        try {
            $userDeviceInformation = new UserDeviceInformationDTO(
                ip: $request->ip(),
                device: $request->header('User-Agent')
            );
            $adminLoginDto = new AdminLoginDTOs(
                email: $request->input(key: 'email'),
                password: $request->input(key: 'password'),
            );

            $adminInfo = $this->authService->login(
                credentials: $adminLoginDto,
                userDeviceInformation: $userDeviceInformation
            );

            return $this->respondWithJson(content: $adminInfo->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function verifyOTPAfterLogin(VerifyEmailOTPAfterLoginRequest $request): JsonResponse
    {
        try {
            $adminInfo = $this->authService->verifyOTPAfterLogin(
                code: $request->input(key: 'verify_code'),
                email: $request->input(key: 'email')
            );

            return $this->respondWithJson(content: $adminInfo->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $registerParams = new RegisterParamsDTO(
                name: $request->input(key: 'username'),
                email: $request->input(key: 'email'),
                password: Hash::make($request->input(key: 'password')),
                role: UserRoleEnum::ADMIN,
            );

            [$otpId, $tokenRegister] = $this->authService->register(
                registerParams: $registerParams,
            );

            return $this->respondWithJson(content: ['token' => $tokenRegister, 'otp_id' => $otpId]);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function verifyOTPAfterRegister(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $verifyEmail = $this->authService->verifyOTPAfterRegister(
                otpId: $request->input(key: 'otp_id'),
                token: $request->input(key: 'token'),
                code: $request->input(key: 'verify_code'),
            );

            return $this->respondWithJson(content: $verifyEmail->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function resendVerifyEmail(string $otpId): JsonResponse
    {
        try {
            $this->authService->resendVerifyEmail(otpId: $otpId);

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->forgotPassword(email: $request->input(key: 'email'));

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword(
                userId: $request->input(key: 'user_id'),
                token: $request->input(key: 'token'),
                password: $request->input(key: 'password'),
            );

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }
}
