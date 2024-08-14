<?php

namespace App\DTOs\Auth;

class AdminLoginDTOs
{
    public function __construct(
        private readonly string $email,
        private readonly string $password,
        private readonly bool $rememberMe
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRememberMe(): bool
    {
        return $this->rememberMe;
    }
}
