<?php

namespace App\Http\Controllers\Auth;

use App\Actions\AuditAuthEvent;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Ativação do 2FA TOTP (RFC 6238) via Laragear — rotas em routes/web.php.
 *
 * O segredo fica criptografado na tabela two_factor_authentications, não no front.
 * A view recebe só o QR em SVG (`toQr()`) e, se precisar, a chave manual.
 *
 * O 2FA só fica "enabled" em confirm(): o usuário prova posse do autenticador
 * com o OTP de 6 dígitos da janela atual (~30 s). Comparação timing-safe no pacote.
 * Mediador não pode desligar o 2FA (requisito 1.5).
 */
class TwoFactorController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $enabled = $user->hasTwoFactorEnabled();

        $qrCode = null;
        $secret = null;

        if (! $enabled) {
            // Gera (ou reaproveita) o segredo TOTP e o QR SVG para o app autenticador.
            $totp = $user->createTwoFactorAuth();
            $qrCode = $totp->toQr();
            $secret = $totp->toString();
        }

        return view('profile.two-factor', [
            'enabled' => $enabled,
            'qrCode' => $qrCode,
            'secret' => $secret,
            'recoveryCodes' => $request->session()->get('two_factor_recovery_codes', []),
            'isMediator' => $user->role === UserRole::Mediador,
        ]);
    }

    /**
     * Confirma o OTP de 6 dígitos. Sem isto o middleware mediator.2fa continua
     * mandando o Mediador de volta para o QR.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (! $user->confirmTwoFactorAuth($validated['code'])) {
            return back()->withErrors([
                'code' => __('O código OTP é inválido. Confira o aplicativo autenticador e tente novamente.'),
            ]);
        }

        $codes = $user->getRecoveryCodes()
            ->map(fn ($item) => is_array($item) ? ($item['code'] ?? reset($item)) : (string) $item)
            ->filter()
            ->values()
            ->all();

        $request->session()->flash('two_factor_recovery_codes', $codes);

        return redirect()
            ->route('two-factor.show')
            ->with('status', __('Autenticação em dois fatores ativada. Guarde os códigos de recuperação.'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Mediador: desativar 2FA burlaria o requisito 1.5.
        if ($user->role === UserRole::Mediador) {
            abort(403, __('Mediadores não podem desativar a autenticação em dois fatores.'));
        }

        if ($user->hasTwoFactorEnabled()) {
            $user->disableTwoFactorAuth();
        }

        AuditAuthEvent::record(
            event: 'two_factor_disabled',
            outcome: 'success',
            email: $user->email,
        );

        return back()->with('status', __('Autenticação em dois fatores desativada.'));
    }

    public function recoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasTwoFactorEnabled(), 403);

        $codes = $user->generateRecoveryCodes()
            ->map(fn ($item) => is_array($item) ? ($item['code'] ?? reset($item)) : (string) $item)
            ->filter()
            ->values()
            ->all();

        $request->session()->flash('two_factor_recovery_codes', $codes);

        return back()->with('status', __('Novos códigos de recuperação foram gerados. Os anteriores deixaram de valer.'));
    }
}
