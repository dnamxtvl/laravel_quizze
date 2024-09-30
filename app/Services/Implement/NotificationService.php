<?php

namespace App\Services\Implement;

use App\DTOs\Notification\ListNotifyPaginateDTO;
use App\Repository\Interface\NotificationRepositoryInterface;
use App\Services\Interface\NotificationServiceInterface;
use Illuminate\Support\Facades\Auth;

readonly class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    public function listNotify(?string $latestNotifyId): ListNotifyPaginateDTO
    {
        $authId = Auth::id();
        $countNotifyUnread = $this->notificationRepository->countUnreadNotify(userId: $authId);
        $listNotify = $this->notificationRepository->listNotify(userId: $authId, latestNotifyId: $latestNotifyId);

        return new ListNotifyPaginateDTO(
            countUnread: $countNotifyUnread,
            listNotify: $listNotify
        );
    }

    public function deleteNotify(string $notifyId): void
    {
        $notify = $this->notificationRepository->findById(notifyId: $notifyId);
        if ($notify) {
            $this->notificationRepository->deleteNotify(notification: $notify);
        }
    }
}
