<?php

namespace App\Http\Controllers\Auth;

use App\Actions\AuditAuthEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        if (! $this->resetTokenIsValid($request->query('email'), $request->route('token'))) {
            $this->auditInvalidToken($request->query('email'), 'link_opened');

            return view('auth.reset-password-invalid');
        }

        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|View
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => $request->password,
                    'remember_token' => Str::random(60),
                ])->save();

                DB::table('sessions')->where('user_id', $user->id)->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        $reason = match ($status) {
            Password::INVALID_TOKEN => 'invalid_or_expired_token',
            Password::INVALID_USER => 'unknown_user',
            default => (string) $status,
        };

        AuditAuthEvent::record(
            event: 'password_reset',
            outcome: 'failure',
            email: $request->string('email')->toString(),
            meta: ['reason' => $reason],
        );

        return view('auth.reset-password-invalid');
    }

    protected function resetTokenIsValid(?string $email, mixed $token): bool
    {
        if (! is_string($email) || $email === '' || ! is_string($token) || $token === '') {
            return false;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return false;
        }

        return Password::tokenExists($user, $token);
    }

    protected function auditInvalidToken(?string $email, string $source): void
    {
        AuditAuthEvent::record(
            event: 'password_reset',
            outcome: 'failure',
            email: $email,
            meta: [
                'reason' => 'invalid_or_expired_token',
                'source' => $source,
            ],
        );
    }
}
