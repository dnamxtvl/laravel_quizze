<?php

namespace App\Repository\Implement;

use App\DTOs\Notification\CreateNotifyDTO;
use App\Models\Notification;
use App\Pipeline\Notification\IsReadFilter;
use App\Pipeline\UserShareQuestion\UserShareIdFilter;
use App\Repository\Interface\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Log;

readonly class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(
        private Notification $notification
    ) {}

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->notification->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new UserShareIdFilter(filters: $filters),
                new IsReadFilter(filters: $filters),
            ])
            ->thenReturn();
    }


    public function createNotify(CreateNotifyDTO $notifyDTO): Notification
    {
        $notify = new Notification();
        $notify->user_id = $notifyDTO->getUserId();
        $notify->type = $notifyDTO->getType()->value;
        $notify->title = $notifyDTO->getTitle();
        $notify->content = $notifyDTO->getContent();
        $notify->link = $notifyDTO->getLink();
        $notify->save();

        return $notify;
    }

    public function listNotify(string $userId, ?string $latestNotifyId = null): Collection
    {
        if (is_null($latestNotifyId)) {
            return $this->notification->query()
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->skip(0)
                ->limit(config('app.notify.limit_pagination'))
                ->get();
        }

        return $this->notification->query()
            ->where('user_id', $userId)
            ->where('id', '<', $latestNotifyId)
            ->limit(config('app.notify.limit_pagination'))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function countUnreadNotify(string $userId): int
    {
        return $this->getQuery(filters: ['user_id' => $userId])
            ->where('is_read', false)
            ->count();
    }

    public function findById(string $notifyId): ?Notification
    {
        return $this->notification->query()->find(id: $notifyId);
    }

    public function readNotify(Notification $notification): void
    {
        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();
    }

    public function deleteNotify(Notification $notification): void
    {
        Log::info("Delete notify: {$notification->id}");
        $notification->delete();
    }
}
