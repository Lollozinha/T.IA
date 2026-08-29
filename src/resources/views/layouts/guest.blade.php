<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'T.IA') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:400,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 sm:pt-0 bg-sky-50">
            <div class="text-center">
                <a href="/" class="inline-flex flex-col items-center gap-2">
                    <x-application-logo class="w-16 h-16 fill-current text-sky-700" />
                    <span class="text-xl font-bold tracking-tight text-sky-800">T.IA</span>
                </a>
                <p class="mt-1 text-sm text-slate-600">Apoio cognitivo para crianças com TEA</p>
            </div>

            <div class="w-full max-w-md mt-6 px-4 sm:px-8 py-6 bg-white shadow-md overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
