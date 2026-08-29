<?php

namespace App\Http\Controllers\Profile;

use App\Actions\AuditAuthEvent;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $enabled = $user->hasTwoFactorEnabled();

        $qrCode = null;
        $secret = null;

        if (! $enabled) {
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
