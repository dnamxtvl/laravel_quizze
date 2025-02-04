<?php

namespace App\Repository\Interface;

use App\DTOs\Auth\SaveEmailVerifyOTPDTO;
use App\DTOs\Auth\TypeCodeOTPEnum;
use App\Models\EmailVerifyOTP;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface EmailVerifyOTPRepositoryInterface
{
    public function getQuery(array $columnSelects = [], array $filters = []): Builder;

    public function save(SaveEmailVerifyOTPDTO $saveEmailVerify): EmailVerifyOTP;

    public function findById(string $emailVerifyOtpId): ?EmailVerifyOTP;

    public function findByCondition(array $filters = []): ?EmailVerifyOTP;

    public function deleteByUserIdAndType(string $userId, TypeCodeOTPEnum $type): bool;
}
