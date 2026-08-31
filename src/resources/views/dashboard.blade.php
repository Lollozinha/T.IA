<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Painel') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                <div class="p-6 text-slate-800">
                    <p class="font-semibold">{{ __('Olá, :name', ['name' => Auth::user()->name]) }}</p>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ Auth::user()->isMediator()
                            ? __('Perfil: Mediador. A autenticação em dois fatores está ativa nesta conta.')
                            : __('Perfil: Responsável. Você está autenticado no T.IA.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
