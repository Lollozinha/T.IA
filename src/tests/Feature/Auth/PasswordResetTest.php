<?php

use App\Models\AuthAuditLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('unknown email does not reveal whether the account exists', function () {
    Notification::fake();

    $response = $this->post('/forgot-password', ['email' => 'nao-existe@tia.test']);

    $response->assertSessionHas('status', __('passwords.sent'));
    Notification::assertNothingSent();
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->get(route('password.reset', [
            'token' => $notification->token,
            'email' => $user->email,
        ]));

        $response->assertOk();
        $response->assertSee('Nova senha');
        $response->assertDontSee('Link inválido ou expirado');

        return true;
    });
});

test('opening an expired reset link is blocked without showing the form', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->travel(16)->minutes();

        $response = $this->get(route('password.reset', [
            'token' => $notification->token,
            'email' => $user->email,
        ]));

        $response->assertOk();
        $response->assertSee('Link inválido ou expirado');
        $response->assertDontSee('Nova senha');
        $this->assertGuest();

        return true;
    });
});

test('opening a used reset link is blocked without showing the form', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])->assertRedirect(route('login'));

        $response = $this->get(route('password.reset', [
            'token' => $notification->token,
            'email' => $user->email,
        ]));

        $response->assertOk();
        $response->assertSee('Link inválido ou expirado');
        $response->assertDontSee('Nova senha');

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('reset token cannot be reused', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])->assertRedirect(route('login'));

        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Password2',
            'password_confirmation' => 'Password2',
        ])->assertOk()->assertSee('Link inválido ou expirado');

        return true;
    });
});

test('expired reset token is rejected', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->travel(16)->minutes();

        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])->assertOk()->assertSee('Link inválido ou expirado');

        $this->assertGuest();

        return true;
    });
});

test('successful password reset deletes persisted sessions for that user', function () {
    Notification::fake();

    $user = User::factory()->create();

    DB::table('sessions')->insert([
        'id' => 'test-session-'.$user->id,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])->assertRedirect(route('login'));

        expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);

        return true;
    });
});

test('password reset success is written once to the audit table', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])->assertRedirect(route('login'));

        expect(AuthAuditLog::query()->where('event', 'password_reset')->where('outcome', 'success')->count())->toBe(1);

        return true;
    });
});
