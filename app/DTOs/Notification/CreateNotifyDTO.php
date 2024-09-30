<?php

namespace App\DTOs\Notification;

use App\Enums\Notification\TypeNotifyEnum;

readonly class CreateNotifyDTO
{
    public function __construct(
        private string $userId,
        private string $title,
        private string $content,
        private TypeNotifyEnum $type,
        private ?string $link = null,
        private ?string $avatarNotify = null,
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getType(): TypeNotifyEnum
    {
        return $this->type;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getAvatarNotify(): ?string
    {
        return $this->avatarNotify;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type->value,
            'link' => $this->link,
            'avatar_notify' => $this->avatarNotify,
        ];
    }
}
