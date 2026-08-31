<x-guest-layout>
    {{-- Cadastro: senha vai para Argon2id (cast hashed). Mediador é redirecionado ao QR 2FA. --}}
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <fieldset class="space-y-2">
            <legend class="text-sm font-medium text-slate-700">{{ __('Perfil') }}</legend>
            <label class="flex items-center gap-2 min-h-11 rounded-lg border border-slate-200 px-3">
                <input type="radio" name="role" value="responsavel" class="text-sky-700 focus:ring-sky-600" @checked(old('role', 'responsavel') === 'responsavel')>
                <span class="text-sm">{{ __('Responsável') }}</span>
            </label>
            <label class="flex items-center gap-2 min-h-11 rounded-lg border border-slate-200 px-3">
                <input type="radio" name="role" value="mediador" class="text-sky-700 focus:ring-sky-600" @checked(old('role') === 'mediador')>
                <span class="text-sm">{{ __('Mediador') }}</span>
            </label>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </fieldset>

        <div>
            <x-input-label for="password" :value="__('Senha')" />
            <p class="text-xs text-slate-500 mt-1">{{ __('Mínimo 8 caracteres, com letras maiúsculas, minúsculas e números.') }}</p>
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar senha')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
            <a class="inline-flex min-h-11 items-center justify-center text-sm text-sky-800 underline hover:text-sky-950 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-600" href="{{ route('login') }}">
                {{ __('Já possui conta? Entrar') }}
            </a>

            <x-primary-button>
                {{ __('Cadastrar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
