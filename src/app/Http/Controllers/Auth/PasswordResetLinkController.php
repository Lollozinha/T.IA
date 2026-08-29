<?php

namespace App\Http\Controllers\Auth;

use App\Actions\AuditAuthEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            $reason = match ($status) {
                Password::RESET_THROTTLED => 'throttled',
                Password::INVALID_USER => 'unknown_user',
                default => (string) $status,
            };

            AuditAuthEvent::record(
                event: 'password_reset_link_sent',
                outcome: 'failure',
                email: $request->string('email')->toString(),
                meta: ['reason' => $reason],
            );
        }

        return back()->with('status', __(Password::RESET_LINK_SENT));
    }
}
