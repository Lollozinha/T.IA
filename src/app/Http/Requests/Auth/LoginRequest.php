<?php

namespace App\Http\Requests\Auth;

use App\Actions\AuditAuthEvent;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laragear\TwoFactor\Facades\Auth2FA;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isTwoFactorChallenge()) {
            return [
                '2fa_code' => ['nullable', 'string'],
                'recovery_code' => ['nullable', 'string'],
            ];
        }

        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if ($code = $this->input('recovery_code') ?: $this->input('2fa_code')) {
            $this->merge(['2fa_code' => $code]);
        }

        try {
            $attempted = Auth2FA::attempt(
                array_filter(
                    $this->only('email', 'password'),
                    fn ($value) => $value !== null && $value !== '',
                ),
                $this->boolean('remember'),
            );
        } catch (HttpResponseException $exception) {
            if ($this->filled('2fa_code') || $this->filled('recovery_code')) {
                RateLimiter::hit($this->twoFactorThrottleKey(), $this->decaySeconds());
                AuditAuthEvent::record(
                    event: 'two_factor_challenge_failed',
                    outcome: 'failure',
                    email: $this->input('email'),
                );
                $this->ensureIsNotRateLimited();
            }

            throw $exception;
        }

        if (! $attempted) {
            RateLimiter::hit($this->emailThrottleKey(), $this->decaySeconds());
            RateLimiter::hit($this->ipThrottleKey(), $this->decaySeconds());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->emailThrottleKey());
        RateLimiter::clear($this->ipThrottleKey());
        RateLimiter::clear($this->twoFactorThrottleKey());
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $maxAttempts = $this->maxAttempts();

        $limited = RateLimiter::tooManyAttempts($this->ipThrottleKey(), $maxAttempts)
            || RateLimiter::tooManyAttempts($this->twoFactorThrottleKey(), $maxAttempts);

        if ($this->filled('email')) {
            $limited = $limited || RateLimiter::tooManyAttempts($this->emailThrottleKey(), $maxAttempts);
        }

        if (! $limited) {
            return;
        }

        event(new Lockout($this));

        $seconds = max(
            RateLimiter::availableIn($this->ipThrottleKey()),
            RateLimiter::availableIn($this->emailThrottleKey()),
            RateLimiter::availableIn($this->twoFactorThrottleKey()),
        );

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return $this->emailThrottleKey().'|'.$this->ipThrottleKey();
    }

    protected function isTwoFactorChallenge(): bool
    {
        return $this->filled('2fa_code')
            || $this->filled('recovery_code')
            || $this->session()->has(config('two-factor.login.key', '_2fa_login'));
    }

    protected function maxAttempts(): int
    {
        return (int) env('LOGIN_MAX_ATTEMPTS', 5);
    }

    protected function decaySeconds(): int
    {
        return (int) env('LOGIN_DECAY_SECONDS', 7200);
    }

    protected function emailThrottleKey(): string
    {
        return 'login-email:'.Str::transliterate(Str::lower((string) $this->input('email')));
    }

    protected function ipThrottleKey(): string
    {
        return 'login-ip:'.$this->ip();
    }

    protected function twoFactorThrottleKey(): string
    {
        return 'login-2fa:'.$this->ip();
    }
}
