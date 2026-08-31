<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Sessão web: login (delegado ao LoginRequest) e logout com destruição no MySQL.
 *
 * SESSION_DRIVER=database + SESSION_ENCRYPT=true: o payload fica na tabela
 * `sessions`, cifrado. Dá para invalidar no logout e no reset de senha (1.10).
 */
class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Novo ID de sessão após login bem-sucedido (mitiga session fixation).
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Logout: encerra a sessão atual e apaga TODAS as linhas do usuário em `sessions`.
     * Evidência 1.8: SELECT na tabela antes/depois do botão Sair deve zerar o user_id.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = $request->user()?->id;

        Auth::guard('web')->logout();

        if ($userId) {
            // Outros navegadores/dispositivos do mesmo usuário também saem.
            DB::table('sessions')->where('user_id', $userId)->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
