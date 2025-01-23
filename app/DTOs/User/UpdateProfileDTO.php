<?php

namespace App\DTOs\User;

use Illuminate\Http\UploadedFile;

class UpdateProfileDTO
{
    public function __construct(
        private readonly string $userId,
        private readonly string $name,
        private readonly ?UploadedFile $avatar = null,
        private ?string $path = null,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
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
