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
                transform: scale(1.05);
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
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .user-card:active {
            transform: translateY(-2px) scale(0.98);
        }

        .emoji {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .user-card:hover .emoji {
            animation: pulse-gentle 1s ease-in-out infinite;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
            min-height: 100vh;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <!-- Contenedor principal -->
    <div class="w-full max-w-5xl">

        <!-- Header -->
        <div class="text-center mb-12 fade-in-up" style="animation-delay: 0.1s;">
            <h1 class="text-5xl font-bold text-white mb-3 drop-shadow-lg">🏪 Pulpería Alicia</h1>
            <p class="text-xl text-white/90 drop-shadow">¿Quién está trabajando hoy?</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-6 fade-in-up glass-effect rounded-xl p-4 text-center" style="animation-delay: 0.2s;">
                <p class="text-sm font-medium text-green-600">{{ session('status') }}</p>
            </div>
        @endif

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-6 fade-in-up glass-effect rounded-xl p-4" style="animation-delay: 0.2s;">
                <div class="text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Grid de usuarios -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- MAMI -->
            <form method="POST" action="{{ route('login') }}" class="fade-in-up" style="animation-delay: 0.3s;">
                @csrf
                <input type="hidden" name="email" value="mami@pulperia.com">
                <input type="hidden" name="password" value="">
                <button type="submit" class="user-card w-full glass-effect rounded-2xl p-8 text-center cursor-pointer group">
                    <div class="emoji text-7xl mb-4">👩</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">MAMI</h3>
                    <div class="w-16 h-1 bg-gradient-to-r from-pink-400 to-pink-600 rounded-full mx-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

            <!-- PAPI -->
            <form method="POST" action="{{ route('login') }}" class="fade-in-up" style="animation-delay: 0.4s;">
                @csrf
                <input type="hidden" name="email" value="papi@pulperia.com">
                <input type="hidden" name="password" value="">
                <button type="submit" class="user-card w-full glass-effect rounded-2xl p-8 text-center cursor-pointer group">
                    <div class="emoji text-7xl mb-4">👨</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">PAPI</h3>
                    <div class="w-16 h-1 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full mx-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

            <!-- NATALY -->
            <form method="POST" action="{{ route('login') }}" class="fade-in-up" style="animation-delay: 0.5s;">
                @csrf
                <input type="hidden" name="email" value="nataly@pulperia.com">
                <input type="hidden" name="password" value="">
                <button type="submit" class="user-card w-full glass-effect rounded-2xl p-8 text-center cursor-pointer group">
                    <div class="emoji text-7xl mb-4">👧</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">NATALY</h3>
                    <div class="w-16 h-1 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full mx-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

            <!-- OTROS -->
            <form method="POST" action="{{ route('login') }}" class="fade-in-up" style="animation-delay: 0.6s;">
                @csrf
                <input type="hidden" name="email" value="otros@pulperia.com">
                <input type="hidden" name="password" value="">
                <button type="submit" class="user-card w-full glass-effect rounded-2xl p-8 text-center cursor-pointer group">
                    <div class="emoji text-7xl mb-4">👤</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">OTROS</h3>
                    <div class="w-16 h-1 bg-gradient-to-r from-gray-400 to-gray-600 rounded-full mx-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

        </div>

        <!-- Footer -->
        <div class="text-center mt-12 fade-in-up" style="animation-delay: 0.7s;">
            <p class="text-white/80 text-sm drop-shadow">Haz click en tu nombre para comenzar</p>
        </div>

    </div>

</body>
</html>
