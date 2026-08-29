<x-guest-layout>
    <div class="mb-4 text-sm text-slate-600">
        {{ __('Informe o e-mail da conta. Se ele existir, enviaremos um link para redefinir a senha. O link expira em pouco tempo e só pode ser usado uma vez.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
            <a class="inline-flex min-h-11 items-center justify-center text-sm text-sky-800 underline" href="{{ route('login') }}">
                {{ __('Voltar ao login') }}
            </a>
            <x-primary-button>
                {{ __('Enviar link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
