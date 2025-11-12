<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Requerida - Pulpería Alicia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        .slide-up {
            animation: slideUp 0.4s ease-out;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    <div class="w-full max-w-md">
        <!-- Card principal -->
        <div class="glass-effect rounded-2xl shadow-2xl p-8 slide-up">

            <!-- Icono de seguridad -->
            <div class="flex justify-center mb-6">
                <div class="bg-gradient-to-r from-red-500 to-pink-600 rounded-full p-4 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-12 h-12">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
            </div>

            <!-- Título y descripción -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Verificación Requerida</h1>
                <p class="text-gray-600">Este módulo requiere contraseña para continuar</p>
            </div>

            <!-- Usuario actual -->
            <div class="flex items-center justify-center gap-3 mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                <span class="text-3xl">{{ ['MAMI' => '👩', 'PAPI' => '👨', 'NATALY' => '👧', 'OTROS' => '👤'][Auth::user()->name] ?? '👤' }}</span>
                <div class="text-left">
                    <div class="font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500">Ingresa la contraseña compartida</div>
                </div>
            </div>

            <!-- Formulario de verificación -->
            <!-- DEBUG: Action URL = {{ route('verify.sensitive') }} -->
            <form method="POST" action="{{ route('verify.sensitive') }}" id="verify-form">
                @csrf
                <input type="hidden" name="intended_url" value="{{ $intended_url }}">

                <div class="mb-6">
                    <label for="sensitive_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <input
                        type="password"
                        id="sensitive_password"
                        name="sensitive_password"
                        autocomplete="current-password"
                        autofocus
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Ingresa la contraseña"
                    >

                    <!-- Mensajes de error -->
                    @error('sensitive_password')
                        <div class="mt-2 flex items-center gap-2 text-red-600 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="flex gap-3">
                    <a href="{{ route('caja') }}"
                       class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg text-center transition-all duration-200">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
                        Verificar
                    </button>
                </div>
            </form>

            <!-- Ayuda -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>💡 La verificación se mantendrá por 5 minutos</p>
            </div>

        </div>
    </div>

    <script>
        // Auto-focus en el campo de contraseña
        document.getElementById('sensitive_password').focus();
    </script>

</body>
</html>
