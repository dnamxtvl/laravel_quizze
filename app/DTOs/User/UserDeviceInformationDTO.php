<?php

namespace App\DTOs\User;

readonly class UserDeviceInformationDTO
{
    public function __construct(
        private string  $ip,
        private string  $device,
        private ?string $longitude = null,
        private ?string $latitude = null,
        private ?string $country = null,
        private ?string $city = null,
    ) {
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }
}
