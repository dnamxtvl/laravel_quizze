<?php

namespace App\DTOs\Auth;

readonly class AdminLoginDTOs
{
    public function __construct(
        private string $email,
        private string $password,
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
