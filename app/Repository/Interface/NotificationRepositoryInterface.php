<?php

namespace App\Repository\Interface;

use App\DTOs\Notification\CreateNotifyDTO;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface
{
    public function getQuery(array $columnSelects = [], array $filters = []): Builder;
    public function createNotify(CreateNotifyDTO $notifyDTO): Notification;

    public function listNotify(string $userId, ?string $latestNotifyId = null): Collection;

    public function countUnreadNotify(string $userId): int;

    public function findById(string $notifyId): ?Notification;

    public function readNotify(Notification $notification): void;

    public function deleteNotify(Notification $notification): void;
}
