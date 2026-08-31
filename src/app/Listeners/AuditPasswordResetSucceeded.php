<?php

namespace App\Listeners;

use App\Actions\AuditAuthEvent;
use Illuminate\Auth\Events\PasswordReset;

class AuditPasswordResetSucceeded
{
    public function handle(PasswordReset $event): void
    {
        AuditAuthEvent::record(
            event: 'password_reset',
            outcome: 'success',
            email: $event->user->getEmailForPasswordReset(),
        );
    }
}
