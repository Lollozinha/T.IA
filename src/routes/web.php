<?php

use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'mediator.2fa'])->name('dashboard');

/*
| Mediador: 2FA TOTP obrigatório.
| GET  gera QR SVG (segredo criptografado no banco, não no HTML além do QR).
| POST confirma OTP de 6 dígitos (confirmTwoFactorAuth) — só então o 2FA fica ativo.
| DELETE é bloqueado para Mediador no controller. Rotas do perfil exigem mediator.2fa.
*/
Route::middleware('auth')->group(function () {
    Route::get('/user/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/user/two-factor', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/user/two-factor', [TwoFactorController::class, 'destroy'])->name('two-factor.destroy');
    Route::post('/user/two-factor/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
});

Route::middleware(['auth', 'mediator.2fa'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
