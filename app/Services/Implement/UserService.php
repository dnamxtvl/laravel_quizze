<?php

namespace App\Services\Implement;

use App\DTOs\Auth\RegisterParamsDTO;
use App\DTOs\User\SearchUserDTO;
use App\DTOs\User\UpdateProfileDTO;
use App\DTOs\User\UserChangePasswordLogDTO;
use App\DTOs\User\UserDisableLogDTO;
use App\Enums\User\UserRoleEnum;
use App\Enums\User\UserStatusEnum;
use App\Exceptions\User\UnAuthorizeException;
use App\Models\User;
use App\Repository\Interface\UserRepositoryInterface;
use App\Services\Interface\UserServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function searchUser(SearchUserDTO $searchUser): LengthAwarePaginator
    {
        return $this->userRepository->searchUser(searchUser: $searchUser);
    }

    public function findUser(string $userId): User
    {
        $user = $this->userRepository->findById(userId: $userId);
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy người dùng');
        }

        return $user;
    }

    public function deleteUser(string $userId): void
    {
        $user = $this->userRepository->findById(userId: $userId);
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy người dùng');
        }

        if ($user->super_admin) {
            throw new UnAuthorizeException(message: 'Không thể xóa super admin');
        }

        $this->userRepository->delete(user: $user);
    }

    /**
     * @throws InternalErrorException
     */
    public function disableUser(string $userId): void
    {
        $user = $this->userRepository->findById(userId: $userId);
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy người dùng');
        }

        if ($user->super_admin) {
            throw new UnAuthorizeException(message: 'Không thể vô hiệu hóa super admin');
        }

        if ($user->disabled) {
            throw new UnAuthorizeException(message: 'User đã bị vô hiệu hóa trước đó!');
        }

        $userDisableLog = new UserDisableLogDTO(
            disableBy: Auth::id(),
            userId: $user->id,
            status: UserStatusEnum::DISABLE
        );

        DB::beginTransaction();

        try {
            $this->userRepository->disable(user: $user);
            $this->userRepository->saveDisableLog(userDisableLog: $userDisableLog);
            $user->tokens()->delete();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e);
            throw new InternalErrorException(message: 'Đã xảy ra lỗi!');
        }
    }

    /**
     * @throws InternalErrorException
     */
    public function activeUser(string $userId): void
    {
        $user = $this->userRepository->findById(userId: $userId);
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy người dùng');
        }

        if (!$user->disabled) {
            throw new UnAuthorizeException(message: 'User đang ở trạng trái active');
        }

        DB::beginTransaction();

        try {
            $userDisableLog = new UserDisableLogDTO(
                disableBy: Auth::id(),
                userId: $user->id,
                status: UserStatusEnum::ENABLE
            );

            $this->userRepository->enable(user: $user);
            $this->userRepository->saveDisableLog(userDisableLog: $userDisableLog);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e);
            throw new InternalErrorException(message: 'Đã xảy ra lỗi!');
        }
    }

    public function searchByElk(string $keyword): Collection
    {
        return $this->userRepository->searchByElk(keyword: $keyword);
    }

    /**
     * @throws InternalErrorException
     */
    public function changePassword(UserChangePasswordLogDTO $userChangePasswordLog, string $userId): void
    {
        $authUser = Auth::user();
        $user = $this->userRepository->findById(userId: $userId);
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy người dùng');
        }
        /* @var User $authUser */
        if (($authUser->type != UserRoleEnum::SYSTEM->value || $authUser->id == $user->id) && $userChangePasswordLog->getOldPassword()) {
            if (!Hash::check($userChangePasswordLog->getOldPassword(), $user->password)) {
                throw new BadRequestHttpException(message: 'Mật khẩu cũ không chinh xác!');
            }
        }

        DB::beginTransaction();
        try {
            $this->userRepository->changePassword(user: $user, userChangePasswordLog: $userChangePasswordLog);
            if ($authUser->id == $user->id) {
                $authUser->tokens()->delete();
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e);
            throw new InternalErrorException(message: 'Đã xảy ra lỗi!');
        }
    }

    public function updateProfile(UpdateProfileDTO $updateProfile): User
    {
        $user = $this->userRepository->findById(userId: $updateProfile->getUserId());
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy user!');
        }

        if ($updateProfile->getAvatar()) {
            $path = Storage::disk('s3')->put('avatar', $updateProfile->getAvatar(), 'public');
            $updateProfile->setPath(path: config('filesystems.disks.s3.url'). '/' . $path);
        }

        return $this->userRepository->updateProfile(user: $user, updateProfile: $updateProfile);
    }

    public function getLatestUser(): User
    {
        $user = $this->userRepository->getLatestUser();
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy user!');
        }

        return $user;
    }

    public function countCustomer(): int
    {
        return $this->userRepository->countCustomer();
    }

    public function createUser(RegisterParamsDTO $registerParams): void
    {
        if ($registerParams->getAvatar()) {
            $path = Storage::disk('s3')->put('avatar', $registerParams->getAvatar(), 'public');
            $registerParams->setPath(path: config('filesystems.disks.s3.url'). '/' . $path);
        }

        $this->userRepository->create(registerParams: $registerParams);
    }
}
