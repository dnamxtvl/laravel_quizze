<?php

namespace App\DTOs\Auth;

use App\Enums\User\UserRoleEnum;
use Carbon\Carbon;

readonly class RegisterParamsDTO
{
    public function __construct(
        private string $name,
        private string $email,
        private string $password,
        private UserRoleEnum $role,
        private ?Carbon $emailVerifiedAt = null,
        private ?string $googleId = null,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return UserRoleEnum
     */
    public function getUserRole(): UserRoleEnum
    {
        return $this->role;
    }

    /**
     * @return Carbon|null
     */
    public function getEmailVerifiedAt(): ?Carbon
    {
        return $this->emailVerifiedAt;
    }

    /**
     * @return string|null
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }
}
