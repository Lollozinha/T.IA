<?php

namespace App\Http\Controllers\Auth;

use App\Actions\AuditAuthEvent;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Cadastro: senha nunca é persistida em claro.
 *
 * O cast `password => hashed` no model User chama Hash::make() com o driver
 * Argon2id (HASH_DRIVER). O digest PHC vai para users.password, no formato
 * $argon2id$v=19$m=65536,t=4,p=1$<salt>$<hash> — salt único embutido (1.1–1.4).
 *
 * Política: mínimo 8 caracteres, maiúscula, minúscula e número (Password::defaults).
 * Mediador recém-criado ainda não tem 2FA; o middleware manda para o QR.
 */
class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // `$request->password` em claro só vive neste request; o cast hashed grava o Argon2id.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->enum('role', UserRole::class),
            'password' => $request->password,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
