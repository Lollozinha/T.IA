<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('mediator with two factor enabled is challenged after valid password', function () {
    $user = User::factory()->mediator()->create();
    $user->createTwoFactorAuth();
    expect($user->confirmTwoFactorAuth($user->makeTwoFactorCode()))->toBeTrue();

    $this->travel(31)->seconds();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertOk();
    $response->assertSee('Verificação em dois fatores');
});

test('mediator completes login with a valid otp after password', function () {
    $user = User::factory()->mediator()->create();
    $user->createTwoFactorAuth();
    expect($user->confirmTwoFactorAuth($user->makeTwoFactorCode()))->toBeTrue();

    $this->travel(31)->seconds();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $this->assertGuest();

    $response = $this->post('/login', [
        '2fa_code' => $user->fresh()->makeTwoFactorCode(),
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('password hashes include algorithm salt and cost in the digest', function () {
    $hash = Hash::make('Password1');

    expect($hash)->not->toBe('Password1');
    expect(Hash::check('Password1', $hash))->toBeTrue();
    expect(Hash::info($hash)['algoName'] ?? '')->not->toBe('');
});

test('identical passwords produce distinct salted hashes', function () {
    $first = Hash::make('Password1');
    $second = Hash::make('Password1');

    expect($first)->not->toBe($second);
    expect(Hash::check('Password1', $first))->toBeTrue();
    expect(Hash::check('Password1', $second))->toBeTrue();
});
