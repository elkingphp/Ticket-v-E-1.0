<?php

namespace App\Observers;

use Modules\Users\Domain\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Avoid recursion if already updating score
        if ($user->isDirty(['profile_completion_score', 'security_risk_level']) && !$user->isDirty(['two_factor_confirmed_at'])) {
            return;
        }

        if ($user->wasChanged(['first_name', 'last_name', 'email', 'phone', 'avatar', 'two_factor_confirmed_at'])) {
            $user->updateSecurityScore();
        }
    }


    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}