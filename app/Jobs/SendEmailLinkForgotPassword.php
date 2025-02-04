<?php

namespace App\Jobs;

use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerAlias;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class SendEmailLinkForgotPassword implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $email
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $status = Password::sendResetLink(['email' => $this->email]);
        if ($status != PasswordBrokerAlias::RESET_LINK_SENT) {
            Log::error('Send reset link failed: ' . $status);
        }
    }
}
