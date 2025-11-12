<x-app-layout>
    <x-slot name="header">
        🏠 Inicio
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Mensaje de bienvenida GRANDE y AMIGABLE -->
            <div class="mb-10 text-center">
                <h1 class="text-5xl font-bold text-gray-800 mb-4">
                    ¡Hola, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="text-2xl text-gray-600">¿Qué deseas hacer hoy?</p>
            </div>

            <!-- Accesos rápidos - BOTONES MUY GRANDES -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Caja -->
                <a href="{{ route('caja') }}"
                   class="group bg-gradient-to-br from-blue-100 to-blue-200 rounded-3xl p-10 shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 border-4 border-blue-300">
                    <div class="text-center">
                        <div class="text-8xl mb-6 group-hover:scale-110 transition-transform">🛒</div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-3">Caja</h3>
                        <p class="text-lg text-gray-600">Realizar ventas</p>
                    </div>
                </a>

                <!-- Inventario -->
                <a href="{{ route('inventario.index') }}"
                   class="group bg-gradient-to-br from-purple-100 to-purple-200 rounded-3xl p-10 shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 border-4 border-purple-300">
                    <div class="text-center">
                        <div class="text-8xl mb-6 group-hover:scale-110 transition-transform">📦</div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-3">Inventario</h3>
                        <p class="text-lg text-gray-600">Ver productos</p>
                    </div>
                </a>

                <!-- Finanzas -->
                <a href="{{ route('finanzas.index') }}"
                   class="group bg-gradient-to-br from-green-100 to-green-200 rounded-3xl p-10 shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 border-4 border-green-300">
                    <div class="text-center">
                        <div class="text-8xl mb-6 group-hover:scale-110 transition-transform">💰</div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-3">Finanzas</h3>
                        <p class="text-lg text-gray-600">Ver dinero</p>
                    </div>
                </a>

                <!-- Reporte de Ventas -->
                <a href="{{ route('reportes.ventas.index') }}"
                   class="group bg-gradient-to-br from-orange-100 to-orange-200 rounded-3xl p-10 shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 border-4 border-orange-300">
                    <div class="text-center">
                        <div class="text-8xl mb-6 group-hover:scale-110 transition-transform">📈</div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-3">Reportes</h3>
                        <p class="text-lg text-gray-600">Ver ventas</p>
                    </div>
                </a>

                <!-- Créditos -->
                <a href="{{ route('reportes.cxc') }}"
                   class="group bg-gradient-to-br from-pink-100 to-pink-200 rounded-3xl p-10 shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 border-4 border-pink-300">
                    <div class="text-center">
                        <div class="text-8xl mb-6 group-hover:scale-110 transition-transform">💳</div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-3">Créditos</h3>
                        <p class="text-lg text-gray-600">Ver deudas</p>
                    </div>
                </a>

                <!-- Proveedores -->
                <a href="{{ route('proveedores.index') }}"
                   class="group bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-3xl p-10 shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 border-4 border-yellow-300">
                    <div class="text-center">
                        <div class="text-8xl mb-6 group-hover:scale-110 transition-transform">🚚</div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-3">Proveedores</h3>
                        <p class="text-lg text-gray-600">Ver proveedores</p>
                    </div>
                </a>

            </div>

            <!-- Mensaje motivacional -->
            <div class="mt-12 text-center">
                <div class="inline-block bg-white/80 backdrop-blur-sm rounded-3xl px-10 py-6 shadow-lg border-4 border-purple-200">
                    <p class="text-2xl text-gray-700 font-semibold">
                        ✨ ¡Que tengas un excelente día de trabajo! ✨
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
