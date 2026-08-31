<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Política de senha (cadastro, troca e reset): 8+ com maiúscula, minúscula e número.
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        // Teto extra anti-flood (não substitui o lockout de 5 falhas / 2 h no LoginRequest).
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}
