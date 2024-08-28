<?php

namespace App\Http\Controllers;

use App\DTOs\Auth\AdminLoginDTOs;
use App\Http\Requests\AdminLoginRequest;
use App\Services\Interface\AuthServiceInterface;
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
            $adminLoginDto = new AdminLoginDTOs(
                email: $request->input(key: 'email'),
                password: $request->input(key: 'password'),
            );

            $adminInfo = $this->authService->login(credentials: $adminLoginDto);

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
}
