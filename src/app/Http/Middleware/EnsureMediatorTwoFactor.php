<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
