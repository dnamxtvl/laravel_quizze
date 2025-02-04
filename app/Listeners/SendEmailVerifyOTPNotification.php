<?php

namespace App\Listeners;

use App\DTOs\Auth\SaveEmailVerifyOTPDTO;
use App\DTOs\Auth\TypeCodeOTPEnum;
use App\Models\User;
use App\Repository\Interface\EmailVerifyOTPRepositoryInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

readonly class SendEmailVerifyOTPNotification implements ShouldQueue
{
    public function __construct(
        private EmailVerifyOTPRepositoryInterface $emailVerifyOTPRepository,
    ) {
    }

    public function handle($event): void
    {
        $user = $event->user;
        /** @var User $user */
        $saveEmailVerifyDTO = new SaveEmailVerifyOTPDTO(
            code: $event->verifyCode,
            userId: $user->id,
            expiredAt: now()->addHour(),
            type: $event->type,
            token: $event->type == TypeCodeOTPEnum::VERIFY_EMAIL ?
                hash('sha256', Str::uuid() . Str::random(40) . $user->id) :
                Password::createToken(user: $user)
        );
        $this->emailVerifyOTPRepository->save(saveEmailVerify: $saveEmailVerifyDTO);
        if ($event->user instanceof MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {
            $user->sendEmailVerifyNotification(verifyCode: $event->verifyCode);
        }
    }
}
