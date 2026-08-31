<?php

namespace App\Listeners;

use App\Actions\AuditAuthEvent;
use Illuminate\Auth\Events\Lockout;

class AuditAuthLockout
{
    public function handle(Lockout $event): void
    {
        AuditAuthEvent::record(
            event: 'login_lockout',
            outcome: 'blocked',
            email: $event->request->input('email'),
            meta: ['minutes' => 120],
        );
    }
}
