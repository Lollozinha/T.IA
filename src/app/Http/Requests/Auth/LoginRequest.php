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

/**
 * Login com proteção de força bruta e segundo fator (TOTP).
 *
 * Fluxo de segurança:
 * 1. Antes de autenticar, verifica lockout (5 falhas → 2 horas, por e-mail E por IP).
 * 2. Auth2FA::attempt() confere e-mail/senha (hash Argon2id via Eloquent `hashed`).
 *    Se o Mediador já tiver 2FA ativo, o pacote NÃO conclui o login: dispara um
 *    desafio OTP (tela de 6 dígitos). Só depois do código válido a sessão nasce.
 * 3. Senha errada incrementa os contadores. Login (e OTP) certo zera tudo.
 */
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
        // No desafio 2FA o e-mail/senha já foram validados; só o OTP (ou recovery) entra.
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
            // Hash::check() (Argon2id) ocorre dentro do provider Eloquent.
            // Com 2FA confirmado, attempt() devolve false/exceção até o OTP bater
            // com o segredo TOTP (janela de 1 período, ~30 s) no Laragear.
            $attempted = Auth2FA::attempt(
                array_filter(
                    $this->only('email', 'password'),
                    fn ($value) => $value !== null && $value !== '',
                ),
                $this->boolean('remember'),
            );
        } catch (HttpResponseException $exception) {
            // OTP/recovery inválido: conta no mesmo teto de 5 tentativas (chave login-2fa:{ip}).
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
            // Credencial primária falhou: incrementa e-mail e IP (lockout duplo).
            RateLimiter::hit($this->emailThrottleKey(), $this->decaySeconds());
            RateLimiter::hit($this->ipThrottleKey(), $this->decaySeconds());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Sucesso: some o bloqueio para este e-mail, IP e desafio 2FA.
        RateLimiter::clear($this->emailThrottleKey());
        RateLimiter::clear($this->ipThrottleKey());
        RateLimiter::clear($this->twoFactorThrottleKey());
    }

    /**
     * Lockout acadêmico: 5 tentativas inválidas → 7200 s (2 h).
     *
     * Qualquer chave no limite bloqueia (e-mail OU IP OU OTP). Assim não dá para
     * contornar trocando só o e-mail (mesmo IP) ou só o IP (mesmo e-mail).
     *
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

        // A view mostra minutos restantes (ceil), exigência 1.11 / evidência 1.8.
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

    /** Teto de falhas consecutivas (padrão 5). */
    protected function maxAttempts(): int
    {
        return (int) env('LOGIN_MAX_ATTEMPTS', 5);
    }

    /** Janela do bloqueio em segundos (padrão 7200 = 2 horas). */
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
