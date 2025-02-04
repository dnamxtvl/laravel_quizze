<?php

namespace App\DTOs\Auth;

use App\Enums\Auth\TypeUserHistoryLoginEnum;

readonly class UserLoginHistoryDTO
{
    public function __construct(
        private string $userId,
        private string $ip,
        private string $device,
        private TypeUserHistoryLoginEnum $type,
        private ?string $longitude = null,
        private ?string $latitude = null,
    ) {
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

    public function getType(): TypeUserHistoryLoginEnum
    {
        return $this->type;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }
}
