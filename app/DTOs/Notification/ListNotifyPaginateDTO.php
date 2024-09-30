<?php

namespace App\DTOs\Notification;

use Illuminate\Database\Eloquent\Collection;

readonly class ListNotifyPaginateDTO
{
    public function __construct(
        private int $countUnread,
        private Collection $listNotify,
    ) {}

    public function getCountUnread(): int
    {
        return $this->countUnread;
    }

    public function getListNotify(): Collection
    {
        return $this->listNotify;
    }

    public function toArray(): array
    {
        return [
            'count_unread' => $this->getCountUnread(),
            'list_notify' => $this->getListNotify(),
        ];
    }
}
