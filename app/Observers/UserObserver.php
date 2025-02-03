<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->searchable();
    }

    public function updated(User $user): void
    {
        $user->searchable();
    }

    public function deleted(User $user): void
    {
        $user->unsearchable();
    }

    public function restored(User $user): void
    {
        $user->searchable();
    }
}
