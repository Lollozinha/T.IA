<?php

namespace App\Listeners;

use App\Actions\AuditAuthEvent;
use Illuminate\Auth\Events\PasswordResetLinkSent;

class AuditPasswordResetLinkSent
{
    public function handle(PasswordResetLinkSent $event): void
    {
        AuditAuthEvent::record(
            event: 'password_reset_link_sent',
            outcome: 'success',
            email: $event->user->getEmailForPasswordReset(),
        );
    }
}
