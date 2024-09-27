<?php

namespace App\Repository\Implement;

use App\DTOs\Notification\CreateNotifyDTO;
use App\Models\Notification;
use App\Pipeline\Global\UserIdFilter;
use App\Repository\Interface\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

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
                new UserIdFilter(filters: $filters),
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
}
