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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>




<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">


    @stack('scripts')
    </head>
 

    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 pt-14 sm:pt-20">

                @include('layouts.navigation')

            <!-- Page Heading - MÁS AMIGABLE -->
            @isset($header)
                <header class="bg-gradient-to-r from-white via-purple-50 to-pink-50 shadow-md border-b-2 border-purple-100">
                    <div class="max-w-7xl mx-auto py-6 px-6 sm:px-8 lg:px-10">
                        <div class="text-2xl font-bold text-gray-800">
                            {{ $header }}
                        </div>
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


<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    </body>
</html>
