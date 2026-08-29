<x-guest-layout>
    <h1 class="text-lg font-semibold text-slate-800 mb-2">{{ __('Verificação em dois fatores') }}</h1>
    <p class="text-sm text-slate-600 mb-4">
        {{ __('Digite o código de 6 dígitos do aplicativo autenticador. Você também pode usar um código de recuperação.') }}
    </p>

    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ useRecovery: false }">
        @csrf

        <div x-show="!useRecovery">
            <x-input-label for="2fa_code" :value="__('Código OTP')" />
            <x-text-input
                id="2fa_code"
                class="block mt-1 w-full tracking-[0.4em] text-center text-lg"
                type="text"
                name="2fa_code"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                pattern="[0-9]*"
                autofocus
            />
            <x-input-error :messages="$errors->get('2fa_code')" class="mt-2" />
        </div>

        <div x-cloak x-show="useRecovery">
            <x-input-label for="recovery_code" :value="__('Código de recuperação')" />
            <x-text-input
                id="recovery_code"
                class="block mt-1 w-full uppercase"
                type="text"
                name="recovery_code"
                autocomplete="off"
            />
            <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
        </div>

        <x-input-error :messages="$errors->get('email')" class="mt-2" />

        <button type="button" class="text-sm text-sky-800 underline min-h-11" @click="useRecovery = !useRecovery">
            <span x-show="!useRecovery">{{ __('Usar código de recuperação') }}</span>
            <span x-cloak x-show="useRecovery">{{ __('Usar código OTP de 6 dígitos') }}</span>
        </button>

        <x-primary-button class="w-full">
            {{ __('Verificar') }}
        </x-primary-button>
    </form>
</x-guest-layout>
