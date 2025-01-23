<?php

namespace App\Repository\Implement;

use App\DTOs\Auth\RegisterParamsDTO;
use App\DTOs\User\SearchUserDTO;
use App\DTOs\User\UpdateProfileDTO;
use App\DTOs\User\UserChangePasswordLogDTO;
use App\DTOs\User\UserDisableLogDTO;
use App\Models\User;
use App\Models\UserChangePasswordLog;
use App\Models\UserDisableLog;
use App\Pipeline\Global\CreatedAtBetweenFilter;
use App\Pipeline\Global\IdsFilter;
use App\Pipeline\User\RoleFilter;
use App\Pipeline\User\StatusFilter;
use App\Repository\Interface\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Hash;

readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private User $user
    ) {}

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->user->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new IdsFilter(filters: $filters),
                new CreatedAtBetweenFilter(filters: $filters),
                new StatusFilter(filters: $filters),
                new RoleFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->user->query()->where('email', $email)->first();
    }

    public function searchUser(SearchUserDTO $searchUser): LengthAwarePaginator
    {
        return $this->getQuery(filters: $searchUser->toArray())
            ->withCount('quizzes')
            ->orderBy('type', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(perPage: config('app.user.limit_pagination'));
    }

    public function findById(string $userId): ?User
    {
        return $this->user->query()->find(id: $userId);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function disable(User $user): void
    {
        $user->disabled = true;
        $user->disabled_at = now();
        $user->save();
    }

    public function saveDisableLog(UserDisableLogDTO $userDisableLog): void
    {
        $newLog = new UserDisableLog();
        $newLog->disabled_by = $userDisableLog->getDisableBy();
        $newLog->user_id = $userDisableLog->getUserId();
        $newLog->status = $userDisableLog->getStatus()->value;

        $newLog->save();
    }

    public function enable(User $user): void
    {
        $user->disabled = false;
        $user->disabled_at = null;
        $user->save();
    }

    public function verifyEmail(User $user): void
    {
        $user->email_verified_at = now();
        $user->save();
    }

    public function create(RegisterParamsDTO $registerParams): User
    {
        $user = new User();

        $user->name = $registerParams->getName();
        $user->email = $registerParams->getEmail();
        $user->password = $registerParams->getPassword();
        $user->type = $registerParams->getUserRole()->value;
        $user->email_verified_at = $registerParams->getEmailVerifiedAt();
        $user->google_id = $registerParams->getGoogleId();
        $user->save();

        return $user;
    }

    public function searchByElk(string $keyword): Collection
    {
        return User::search($keyword)->get();
    }

    public function changePassword(User $user, UserChangePasswordLogDTO $userChangePasswordLog): void
    {
        $user->password = $userChangePasswordLog->getNewPassword();
        $user->save();

        $userChangePassword = new UserChangePasswordLog();
        $userChangePassword->user_id = $user->id;
        $userChangePassword->ip = $userChangePasswordLog->getIp();
        $userChangePassword->user_agent = $userChangePasswordLog->getUserAgent();
        $userChangePassword->old_password = Hash::make($userChangePasswordLog->getOldPassword());
        $userChangePassword->new_password = Hash::make($userChangePasswordLog->getNewPassword());
        $userChangePassword->change_by = $userChangePasswordLog->getChangeBy();
        $userChangePassword->save();
    }

    public function updateProfile(User $user, UpdateProfileDTO $updateProfile): void
    {
        $user->name = $updateProfile->getName();
        $user->avatar = $updateProfile->getPath();
        $user->save();
    }
}
