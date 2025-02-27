<?php

namespace App\Services\Interface;

use App\DTOs\Notification\ListNotifyPaginateDTO;

interface NotificationServiceInterface
{
    public function listNotify(?string $latestNotifyId): ListNotifyPaginateDTO;

    public function deleteNotify(string $notifyId): void;
}
