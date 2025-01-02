<?php

namespace App\DTOs\Auth;

use App\Enums\Auth\TypeForgotPasswordLogEnum;

readonly class UserForgotPasswordLogDTO
{
    public function __construct(
        private string $userId,
        private string $ip,
        private string $device,
        private TypeForgotPasswordLogEnum $type,
        private ?string $longitude = null,
        private ?string $latitude = null,
        private ?string $id = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getType(): TypeForgotPasswordLogEnum
    {
        return $this->type;
    }
}
