<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Laragear\TwoFactor\TwoFactorAuthentication;

/**
 * Conta do T.IA. Senha: cast `hashed` → Argon2id (salt no próprio digest).
 * 2FA: trait TwoFactorAuthentication (Laragear); segredo TOTP fora desta tabela.
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements TwoFactorAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthentication;

    /**
     * `hashed`: Hash::make() na gravação e Hash::check() na leitura/login.
     * Driver efetivo: config/hashing.php → env HASH_DRIVER (argon2id no Docker).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isMediator(): bool
    {
        return $this->role === UserRole::Mediador;
    }
}
