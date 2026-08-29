@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 border-slate-300 focus:border-sky-600 focus:ring-sky-600 rounded-lg shadow-sm w-full']) }}>
