<?php

namespace App\Listeners;

use App\Actions\AuditAuthEvent;
use Illuminate\Auth\Events\Failed;

class AuditLoginFailed
{
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? $event->user?->email;

        AuditAuthEvent::record(
            event: 'login_failed',
            outcome: 'failure',
            email: is_string($email) ? $email : null,
        );
    }
}
