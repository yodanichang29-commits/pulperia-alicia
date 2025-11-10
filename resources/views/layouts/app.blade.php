<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

{{-- Favicons (auth layout) --}}
<link rel="icon" href="{{ asset('brand/favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('brand/favicon-512.png') }}">
<link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">



        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>[x-cloak]{ display:none !important; }</style>
        <style>[x-cloak]{display:none !important}</style>


        <style>[x-cloak]{display:none}</style>


        
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
         @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
    </head>
 

    <body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100 pt-14 sm:pt-16">

                @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
          <main>
    {{-- Antes tenías: {{ $slot }} --}}
    @isset($slot)
        {{ $slot }}
    @else
        @yield('content')
    @endisset
</main>

        </div>
    </body>
</html>
