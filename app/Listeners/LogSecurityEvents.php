<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSecurityEvents
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    //
    }

    /**
     * Handle the event.
     */
    public function handle(\Illuminate\Auth\Events\Failed $event): void
    {
        \Illuminate\Support\Facades\Log::warning('Security: Failed login attempt or 2FA challenge failure.', [
            'user_id' => $event->user ? $event->user->id : 'unknown',
            'credentials' => array_keys($event->credentials),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}