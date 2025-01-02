<?php

namespace App\Repository\Implement;

use App\DTOs\User\SearchUserDTO;
use App\DTOs\User\UserDisableLogDTO;
use App\Models\User;
use App\Models\UserDisableLog;
use App\Pipeline\Global\CreatedAtBetweenFilter;
use App\Pipeline\Global\UserIdsFiler;
use App\Pipeline\User\RoleFilter;
use App\Pipeline\User\StatusFilter;
use App\Repository\Interface\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

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
                new UserIdsFiler(filters: $filters),
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
}
