<?php

namespace App\Events;

use App\DTOs\Auth\TypeCodeOTPEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailNotVerifyEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Model $user,
        public readonly string $verifyCode,
        public readonly TypeCodeOTPEnum $type
    ) {
    }
}
