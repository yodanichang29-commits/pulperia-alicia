<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

{{-- Favicons (root + alternos) --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

<link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('manifest.json') }}"> {{-- opcional si tienes manifest --}}


        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0
            bg-gradient-to-b from-slate-50 via-sky-50 to-indigo-100">



                        {{-- ÚNICO LOGO (ajusta tamaños aquí) --}}
<div class="mb-8 text-center">
  <a href="/" class="focus:outline-none focus:ring-0 inline-block">
    {{-- prueba con px si quieres control fino --}}
    <x-application-logo size="w-[180px] h-[180px]" />
    {{-- alternativas:
    <x-application-logo size="w-44 h-44" />
    <x-application-logo size="w-48 h-48 md:w-56 md:h-56" />
    --}}
  </a>
</div>






            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
