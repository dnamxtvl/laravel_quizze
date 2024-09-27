<?php

namespace App\Repository\Interface;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
}
