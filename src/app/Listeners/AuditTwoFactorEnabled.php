<?php

namespace App\Listeners;

use App\Actions\AuditAuthEvent;
use Laragear\TwoFactor\Events\TwoFactorEnabled;

class AuditTwoFactorEnabled
{
    public function handle(TwoFactorEnabled $event): void
    {
        AuditAuthEvent::record(
            event: 'two_factor_enabled',
            outcome: 'success',
            email: method_exists($event->user, 'getEmailForPasswordReset')
                ? $event->user->getEmailForPasswordReset()
                : null,
        );
    }
}
