<?php

namespace App\Repository\Implement;

use App\DTOs\Auth\SaveEmailVerifyOTPDTO;
use App\Models\EmailVerifyOTP;
use App\Pipeline\Global\TypeFilter;
use App\Pipeline\Global\UserIdFilter;
use App\Pipeline\User\VerifyCodeOTPFilter;
use App\Repository\Interface\EmailVerifyOTPRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use App\DTOs\Auth\TypeCodeOTPEnum;

readonly class EmailVerifyOTPRepository implements EmailVerifyOTPRepositoryInterface
{
    public function __construct(
        private EmailVerifyOTP $emailVerifyOtp
    ) {
    }

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->emailVerifyOtp->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(Pipeline::class)
            ->send($query)
            ->through([
                new VerifyCodeOTPFilter(filters: $filters),
                new UserIdFilter(filters: $filters),
                new TypeFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function save(SaveEmailVerifyOTPDTO $saveEmailVerify): EmailVerifyOTP
    {
        $emailVerifyOtp = new EmailVerifyOTP();

        $emailVerifyOtp->code = $saveEmailVerify->getCode();
        $emailVerifyOtp->user_id = $saveEmailVerify->getUserId();
        $emailVerifyOtp->expired_at = $saveEmailVerify->getExpiredAt();
        $emailVerifyOtp->type = $saveEmailVerify->getType();
        $emailVerifyOtp->token = $saveEmailVerify->getToken();
        $emailVerifyOtp->save();

        return $emailVerifyOtp;
    }

    public function findById(string $emailVerifyOtpId): ?EmailVerifyOTP
    {
        return $this->emailVerifyOtp->query()->find(id: $emailVerifyOtpId);
    }

    public function findByCondition(array $filters = []): ?EmailVerifyOTP
    {
        return $this->getQuery(filters: $filters)->first();
    }

    public function deleteByUserIdAndType(string $userId, TypeCodeOTPEnum $type): bool
    {
        return $this->emailVerifyOtp->query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->delete();
    }
}
