<?php

namespace App\Http\Controllers;

use App\DTOs\User\SearchUserDTO;
use App\Enums\User\UserRoleEnum;
use App\Http\Requests\SearchUserRequest;
use App\Services\Interface\UserServiceInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    public function search(SearchUserRequest $request): JsonResponse
    {
        $searchUser = new SearchUserDTO(
            userIds: $request->input(key: 'user_ids'),
            createdAt: !empty($request->input('start_time')) && !empty($request->input('end_time')) ?
                [$request->input(key: 'start_time'), $request->input(key: 'end_time')] : [],
            disabled: $request->input(key: 'disabled'),
            role: !empty($request->input(key: 'role')) ?
                UserRoleEnum::tryFrom($request->input(key: 'role')) : null,
        );

        try {
            $listUsers = $this->userService->searchUser(searchUser: $searchUser);

            return $this->respondWithJson(content: $listUsers->toArray());
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function detail(string $userId): JsonResponse
    {
        try {
            $user = $this->userService->findUser(userId: $userId);

            return $this->respondWithJson(content: $user->toArray());
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function delete(string $userId): JsonResponse
    {
        try {
            $this->userService->deleteUser(userId: $userId);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function disable(string $userId): JsonResponse
    {
        try {
            $this->userService->disableUser(userId: $userId);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function active(string $userId): JsonResponse
    {
        try {
            $this->userService->activeUser(userId: $userId);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }
}
