<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 w-full sm:w-auto items-center justify-center px-4 py-2.5 bg-sky-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:bg-sky-800 focus:bg-sky-800 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2 transition']) }}>
    {{ $slot }}
</button>
