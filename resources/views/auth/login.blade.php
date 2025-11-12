<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulpería Alicia - Iniciar Sesión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-gentle {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.08);
            }
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .user-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .user-card:hover {
            transform: translateY(-12px) scale(1.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .user-card:active {
            transform: translateY(-4px) scale(1.02);
        }

        .emoji {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .user-card:hover .emoji {
            animation: pulse-gentle 1s ease-in-out infinite;
        }

        body {
            background: linear-gradient(135deg, #bfdbfe 0%, #ddd6fe 50%, #fecaca 100%);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
            min-height: 100vh;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border: 3px solid rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">

    <!-- Contenedor principal -->
    <div class="w-full max-w-6xl">

        <!-- Header - MÁS GRANDE -->
        <div class="text-center mb-16 fade-in-up" style="animation-delay: 0.1s;">
            <h1 class="text-6xl md:text-7xl font-bold text-gray-800 mb-5 drop-shadow-lg">🏪 Pulpería Alicia</h1>
            <p class="text-3xl md:text-4xl text-gray-700 drop-shadow font-semibold">¿Quién está trabajando hoy?</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-8 fade-in-up glass-effect rounded-3xl p-6 text-center shadow-xl" style="animation-delay: 0.2s;">
                <p class="text-xl font-semibold text-green-600">{{ session('status') }}</p>
            </div>
        @endif

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-8 fade-in-up glass-effect rounded-3xl p-6 shadow-xl" style="animation-delay: 0.2s;">
                <div class="text-xl text-red-600 font-semibold">
                    @foreach ($errors->all() as $error)
                        <p>❌ {{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Grid de usuarios - BOTONES MÁS GRANDES -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

            <!-- MAMI -->
            <form method="POST" action="{{ route('login') }}" class="fade-in-up" style="animation-delay: 0.3s;">
                @csrf
                <input type="hidden" name="email" value="mami@pulperia.com">
                <input type="hidden" name="password" value="">
                <button type="submit" class="user-card w-full glass-effect rounded-3xl p-10 text-center cursor-pointer group shadow-xl">
                    <div class="emoji text-8xl md:text-9xl mb-6">👩</div>
                    <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">MAMI</h3>
                    <div class="w-20 h-2 bg-gradient-to-r from-pink-300 to-pink-400 rounded-full mx-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

            <!-- PAPI -->
            <form method="POST" action="{{ route('login') }}" class="fade-in-up" style="animation-delay: 0.4s;">
                @csrf
                <input type="hidden" name="email" value="papi@pulperia.com">
                <input type="hidden" name="password" value="">
                <button type="submit" class="user-card w-full glass-effect rounded-3xl p-10 text-center cursor-pointer group shadow-xl">
                    <div class="emoji text-8xl md:text-9xl mb-6">👨</div>
                    <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">PAPI</h3>
                    <div class="w-20 h-2 bg-gradient-to-r from-blue-300 to-blue-400 rounded-full mx-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

            <!-- NATALY -->
            <form method="POST" action="{{ route('login') }}" class="fade-in-up" style="animation-delay: 0.5s;">
                @csrf
                <input type="hidden" name="email" value="nataly@pulperia.com">
                <input type="hidden" name="password" value="">
                <button type="submit" class="user-card w-full glass-effect rounded-3xl p-10 text-center cursor-pointer group shadow-xl">
                    <div class="emoji text-8xl md:text-9xl mb-6">👧</div>
                    <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">NATALY</h3>
                    <div class="w-20 h-2 bg-gradient-to-r from-purple-300 to-purple-400 rounded-full mx-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

            <!-- OTROS -->
            <form method="POST" action="{{ route('login') }}" class="fade-in-up" style="animation-delay: 0.6s;">
                @csrf
                <input type="hidden" name="email" value="otros@pulperia.com">
                <input type="hidden" name="password" value="">
                <button type="submit" class="user-card w-full glass-effect rounded-3xl p-10 text-center cursor-pointer group shadow-xl">
                    <div class="emoji text-8xl md:text-9xl mb-6">👤</div>
                    <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">OTROS</h3>
                    <div class="w-20 h-2 bg-gradient-to-r from-gray-300 to-gray-400 rounded-full mx-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

        </div>

        <!-- Footer - TEXTO MÁS GRANDE -->
        <div class="text-center mt-16 fade-in-up" style="animation-delay: 0.7s;">
            <div class="inline-block bg-white/80 backdrop-blur-sm rounded-2xl px-8 py-4 shadow-lg">
                <p class="text-gray-700 text-2xl font-semibold">👆 Toca tu nombre para comenzar</p>
            </div>
        </div>

    </div>

</body>
</html>
