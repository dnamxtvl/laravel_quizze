<?php

namespace App\Repository\Interface;

use App\DTOs\Notification\CreateNotifyDTO;
use App\Models\Notification;

interface NotificationRepositoryInterface
{
    public function createNotify(CreateNotifyDTO $notifyDTO): Notification;
}
