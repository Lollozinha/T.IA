<?php

namespace App\Http;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 2FA obrigatório para o perfil Mediador (req. 1.5).
 *
 * Senha sozinha não libera o painel: sem TOTP confirmado, dashboard/perfil
 * redirecionam para o QR. Responsável não passa por esta trava.
 *
 * Alias `mediator.2fa` em bootstrap/app.php.
 */
class EnsureMediatorTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->role === UserRole::Mediador
            && ! $user->hasTwoFactorEnabled()
        ) {
            return redirect()
                ->route('two-factor.show')
                ->with('status', __('Ative a autenticação em dois fatores para continuar.'));
        }

        return $next($request);
    }
}
