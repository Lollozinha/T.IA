<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Autenticação em dois fatores') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-sky-50 border border-sky-200 text-sky-900 rounded-xl px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-2xl p-6 space-y-6">
                @if ($enabled)
                    <p class="text-sm text-slate-700">
                        {{ __('A autenticação em dois fatores está ativa nesta conta.') }}
                    </p>

                    @if (! empty($recoveryCodes))
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="font-semibold text-amber-900 mb-2">{{ __('Guarde estes códigos de recuperação agora.') }}</p>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 font-mono text-sm break-all">
                                @foreach ($recoveryCodes as $code)
                                    <li class="bg-white rounded-md px-3 py-2 border border-amber-100">{{ $code }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                        @csrf
                        <x-primary-button>
                            {{ __('Gerar novos códigos de recuperação') }}
                        </x-primary-button>
                    </form>

                    {{-- Mediador não vê este botão: 2FA é obrigatório (TwoFactorController::destroy aborta 403). --}}
                    @unless ($isMediator)
                        <form method="POST" action="{{ route('two-factor.destroy') }}" onsubmit="return confirm('Desativar 2FA?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-700 underline min-h-11">
                                {{ __('Desativar 2FA') }}
                            </button>
                        </form>
                    @endunless
                @else
                    <p class="text-sm text-slate-700">
                        {{ __('Escaneie o QR Code no Google Authenticator, Authy ou app compatível. Em seguida, confirme com o código de 6 dígitos.') }}
                    </p>

                    {{-- QR SVG gerado pelo Laragear (toQr). O segredo TOTP permanece cifrado no MySQL. --}}
                    @if ($qrCode)
                        <div class="flex justify-center overflow-hidden">
                            <div class="w-full max-w-xs [&_svg]:w-full [&_svg]:h-auto">
                                {!! $qrCode !!}
                            </div>
                        </div>
                    @endif

                    @if ($secret)
                        <p class="text-xs text-slate-500">{{ __('Não consegue escanear? Digite a chave manualmente:') }}</p>
                        <p class="font-mono text-sm break-all bg-slate-50 rounded-lg px-3 py-2 border border-slate-200">{{ $secret }}</p>
                    @endif

                    {{-- Confirmacao: 6 dígitos TOTP (período ~30 s). Sem isto o 2FA não fica enabled. --}}
                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="code" :value="__('Código de 6 dígitos')" />
                            <x-text-input
                                id="code"
                                class="block mt-1 w-full tracking-[0.4em] text-center text-lg"
                                type="text"
                                name="code"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                maxlength="6"
                                required
                            />
                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>
                        <x-primary-button class="w-full sm:w-auto">
                            {{ __('Ativar 2FA') }}
                        </x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
