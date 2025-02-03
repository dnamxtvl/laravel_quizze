<?php

namespace App\Http\Controllers;

use App\DTOs\Auth\RegisterParamsDTO;
use App\DTOs\User\SearchUserDTO;
use App\DTOs\User\UpdateProfileDTO;
use App\DTOs\User\UserChangePasswordLogDTO;
use App\Enums\User\UserRoleEnum;
use App\Http\Requests\AdminCreateUserRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\SearchUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadImageRequest;
use App\Services\Interface\UserServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public function searchByElk(string $keyword): JsonResponse
    {
        try {
            $listUsers = $this->userService->searchByElk(keyword: $keyword);

            return $this->respondWithJson(content: $listUsers->toArray());
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function changePassword(ChangePasswordRequest $request, string $userId): JsonResponse
    {
        try {
            $changePasswordLog = new UserChangePasswordLogDTO(
                userId: $userId,
                ip: $request->ip(),
                userAgent: $request->header(key: 'User-Agent'),
                oldPassword: $request->input(key: 'old_password'),
                newPassword: $request->input(key: 'new_password'),
                changeBy: Auth::id(),
            );
            $this->userService->changePassword(userChangePasswordLog: $changePasswordLog, userId: $userId);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function updateProfile(UpdateProfileRequest $request, string $userId): JsonResponse
    {
        try {
            $updateProfile = new UpdateProfileDTO(
                userId: $userId,
                name: $request->input(key: 'name'),
                avatar: $request->file('avatar'),
            );
            $this->userService->updateProfile(updateProfile: $updateProfile);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function uploadImage(UploadImageRequest $request): JsonResponse
    {
        try {
            $path = Storage::disk('s3')->put('avatar', $request->file('image'), 'public');
            $path = config('filesystems.disks.s3.url'). '/' . $path;

            return $this->respondWithJson(content: ['path' => $path]);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function createUser(AdminCreateUserRequest $request): JsonResponse
    {
        try {
            $createUser = new RegisterParamsDTO(
                name: $request->input(key: 'username'),
                email: $request->input(key: 'email'),
                password: Hash::make($request->input(key: 'password')),
                role: UserRoleEnum::tryFrom((int)$request->input(key: 'type')),
                emailVerifiedAt: now(),
                avatar: $request->file('avatar'),
            );
            $this->userService->createUser(registerParams: $createUser);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }
}
