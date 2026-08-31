<?php

namespace App\Http\Controllers\Auth;

use App\Actions\AuditAuthEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Pedido do link “esqueci a senha” (anti-enumeração).
 *
 * Password::sendResetLink(): se o e-mail existir, grava Hash::make($token) em
 * password_reset_tokens e envia o link. Se não existir (ou throttle 60 s),
 * NÃO revela isso na UI: a mensagem é sempre a genérica RESET_LINK_SENT (2.1).
 * Tentativas indevidas ainda vão para a auditoria (outcome=failure + reason).
 */
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

        // Sempre a mesma resposta visível: não confirma se o e-mail está cadastrado.
        return back()->with('status', __(Password::RESET_LINK_SENT));
    }
}
