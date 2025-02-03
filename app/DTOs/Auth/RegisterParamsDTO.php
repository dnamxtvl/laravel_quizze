<?php

namespace App\DTOs\Auth;

use App\Enums\User\UserRoleEnum;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class RegisterParamsDTO
{
    public function __construct(
        private readonly string $name,
        private readonly string $email,
        private readonly string $password,
        private readonly UserRoleEnum $role,
        private readonly?Carbon $emailVerifiedAt = null,
        private readonly ?string $googleId = null,
        private readonly ?UploadedFile $avatar = null,
        private ?string $path = null
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

    public function getAvatar(): ?UploadedFile
    {
        return $this->avatar;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
