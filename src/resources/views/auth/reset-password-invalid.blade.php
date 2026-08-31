<x-guest-layout>
    {{--
      Token expirado, já usado ou inválido (req. 2.2 / 2.5).
      Sem campos de senha: o link morto não pode ser reaproveitado no front.
    --}}
    <h1 class="text-lg font-semibold text-slate-800 mb-2">
        {{ __('Link inválido ou expirado') }}
    </h1>
    <p class="text-sm text-slate-600 mb-4">
        {{ __('Este link de redefinição não pode ser usado. Ele vale por 15 minutos e só funciona uma vez. Se já expirou ou já foi usado, solicite um novo e-mail.') }}
    </p>

    <a href="{{ route('password.request') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-sky-700 px-4 text-sm font-semibold text-white hover:bg-sky-800">
        {{ __('Solicitar novo link') }}
    </a>
</x-guest-layout>
