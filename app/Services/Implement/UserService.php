<?php

namespace App\Services\Implement;

use App\DTOs\User\SearchUserDTO;
use App\DTOs\User\UserChangePasswordLogDTO;
use App\DTOs\User\UserDisableLogDTO;
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

        $userDisableLog = new UserDisableLogDTO(
            disableBy: Auth::id(),
            userId: $user->id,
            status: UserStatusEnum::DISABLE
        );

        DB::beginTransaction();

        try {
            $this->userRepository->disable(user: $user);
            $this->userRepository->saveDisableLog(userDisableLog: $userDisableLog);

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
    public function changePassword(UserChangePasswordLogDTO $userChangePasswordLog): void
    {
        $user = Auth::user();
        /* @var User $user */
        if (Hash::check($userChangePasswordLog->getOldPassword(), $user->password)) {
            throw new BadRequestHttpException(message: 'Mật khẩu cũ không chinh xác!');
        }

        DB::beginTransaction();
        try {
            $this->userRepository->changePassword(user: $user, userChangePasswordLog: $userChangePasswordLog);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e);
            throw new InternalErrorException(message: 'Đã xảy ra lỗi!');
        }
    }
}
