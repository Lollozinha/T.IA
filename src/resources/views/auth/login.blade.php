<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="remember_me" class="inline-flex items-center min-h-11">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-700 shadow-sm focus:ring-sky-600" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Lembrar de mim') }}</span>
            </label>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
            @if (Route::has('password.request'))
                <a class="inline-flex min-h-11 items-center justify-center text-sm text-sky-800 underline hover:text-sky-950 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-600" href="{{ route('password.request') }}">
                    {{ __('Esqueci a senha') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Entrar') }}
            </x-primary-button>
        </div>

        <p class="text-center text-sm text-slate-600 pt-2">
            {{ __('Ainda não tem conta?') }}
            <a class="font-semibold text-sky-800 underline" href="{{ route('register') }}">{{ __('Criar conta') }}</a>
        </p>
    </form>
</x-guest-layout>
