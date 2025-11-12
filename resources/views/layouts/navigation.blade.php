<nav x-data="{ open: false, showReportes: false, showMas: false }"
     class="sticky top-0 z-50 bg-gradient-to-r from-blue-50 via-purple-50 to-pink-50 border-b-2 border-purple-200 shadow-lg">

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">

                <!-- Navigation Links (DESKTOP) - MÁS GRANDES Y SIMPLES -->
                <div class="hidden space-x-3 sm:flex items-center">

                    {{-- CAJA - Lo más importante --}}
                    <x-nav-link :href="route('caja')" :active="request()->routeIs('caja')"
                                class="rounded-2xl px-5 py-3 text-lg font-semibold hover:scale-105 transition-transform">
                        <span class="inline-flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" class="w-7 h-7 stroke-2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 3h1.386c.51 0 .955.343 1.091.835l.383 1.44m0 0L6.75 12.75m-1.64-7.475h13.78a.75.75 0 01.73.93l-1.28 5.12a1.5 1.5 0 01-1.46 1.15H7.17m0 0l-.42-1.68M7.5 20.25a.75.75 0 100-1.5.75.75 0 000 1.5zm9 0a.75.75 0 100-1.5.75.75 0 000 1.5z"/>
                            </svg>
                            <span>🛒 Caja</span>
                        </span>
                    </x-nav-link>

                    {{-- INVENTARIO --}}
                    <x-nav-link :href="route('inventario.index')" :active="request()->routeIs('inventario.*')"
                                class="rounded-2xl px-5 py-3 text-lg font-semibold hover:scale-105 transition-transform">
                        <span class="inline-flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" class="w-7 h-7 stroke-2">
                                <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                                <path d="M3 10h18"></path>
                                <path d="M9 16h6"></path>
                            </svg>
                            <span>📦 Inventario</span>
                        </span>
                    </x-nav-link>

                    {{-- FINANZAS --}}
                    <x-nav-link :href="route('finanzas.index')" :active="request()->routeIs('finanzas.*')"
                                class="rounded-2xl px-5 py-3 text-lg font-semibold hover:scale-105 transition-transform">
                        <span class="inline-flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>💰 Finanzas</span>
                        </span>
                    </x-nav-link>

                    {{-- REPORTES - Menú desplegable --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="inline-flex items-center gap-3 px-5 py-3 text-lg font-semibold rounded-2xl transition-all
                                       {{ request()->routeIs('reportes.*') ? 'bg-purple-200 text-purple-800' : 'text-gray-700 hover:bg-white/60' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M9 17V9M13 17v-6M17 17v-3"/>
                            </svg>
                            <span>📊 Reportes</span>
                            <svg class="w-5 h-5 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border-2 border-purple-200 overflow-hidden z-50"
                             style="display: none;">
                            <a href="{{ route('reportes.ventas.index') }}"
                               class="block px-6 py-4 text-base font-medium hover:bg-purple-50 transition-colors
                                      {{ request()->routeIs('reportes.ventas.*') ? 'bg-purple-100 text-purple-800' : 'text-gray-700' }}">
                                📈 Reporte de Ventas
                            </a>
                            <a href="{{ route('reportes.cxc') }}"
                               class="block px-6 py-4 text-base font-medium hover:bg-purple-50 transition-colors border-t border-purple-100
                                      {{ request()->routeIs('reportes.cxc') ? 'bg-purple-100 text-purple-800' : 'text-gray-700' }}">
                                💳 Créditos
                            </a>
                        </div>
                    </div>

                    {{-- MÁS - Menú desplegable --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="inline-flex items-center gap-3 px-5 py-3 text-lg font-semibold rounded-2xl transition-all text-gray-700 hover:bg-white/60">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            <span>⚙️ Más</span>
                            <svg class="w-5 h-5 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             class="absolute left-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border-2 border-purple-200 overflow-hidden z-50"
                             style="display: none;">
                            <a href="{{ route('dashboard') }}"
                               class="block px-6 py-4 text-base font-medium hover:bg-blue-50 transition-colors
                                      {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                                🏠 Dashboard
                            </a>
                            <a href="{{ route('proveedores.index') }}"
                               class="block px-6 py-4 text-base font-medium hover:bg-blue-50 transition-colors border-t border-purple-100
                                      {{ request()->routeIs('proveedores.*') ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                                🚚 Proveedores
                            </a>
                            <a href="{{ route('cash-movements.index') }}"
                               class="block px-6 py-4 text-base font-medium hover:bg-blue-50 transition-colors border-t border-purple-100
                                      {{ request()->routeIs('cash-movements.*') ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                                💵 Movimientos de Caja
                            </a>
                            <a href="{{ route('ingresos.index') }}"
                               class="block px-6 py-4 text-base font-medium hover:bg-blue-50 transition-colors border-t border-purple-100
                                      {{ request()->routeIs('ingresos.*') ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                                📦 Movimientos Inventario
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Settings Dropdown (desktop) -->
            <div class="hidden sm:flex sm:items-center sm:gap-4">
                <!-- Usuario actual - MÁS GRANDE -->
                <div class="flex items-center gap-3 px-5 py-3 bg-gradient-to-r from-green-100 to-emerald-100 rounded-2xl border-2 border-green-300 shadow-md">
                    <span class="text-3xl">{{ ['MAMI' => '👩', 'PAPI' => '👨', 'NATALY' => '👧', 'OTROS' => '👤'][Auth::user()->name] ?? '👤' }}</span>
                    <span class="font-bold text-gray-800 text-lg">{{ Auth::user()->name }}</span>
                </div>

                <!-- Botón Cerrar Sesión - MÁS GRANDE Y SUAVE -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-rose-300 to-pink-300 hover:from-rose-400 hover:to-pink-400 text-gray-800 font-bold text-lg rounded-2xl shadow-lg transition-all duration-200 hover:shadow-xl hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Salir</span>
                    </button>
                </form>
            </div>

            <!-- Hamburger (mobile) - MÁS GRANDE -->
            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-3 rounded-xl text-gray-600 hover:text-gray-800 hover:bg-white/60 transition-all">
                    <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (MÓVIL) - MÁS GRANDE -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t-2 border-purple-200">
        <div class="pt-3 pb-3 space-y-2 px-4">

            {{-- Caja --}}
            <a href="{{ route('caja') }}"
               class="flex items-center gap-4 px-5 py-4 rounded-2xl text-lg font-semibold transition-all
                      {{ request()->routeIs('caja') ? 'bg-purple-100 text-purple-800' : 'text-gray-700 hover:bg-purple-50' }}">
                <span class="text-2xl">🛒</span>
                <span>Caja</span>
            </a>

            {{-- Inventario --}}
            <a href="{{ route('inventario.index') }}"
               class="flex items-center gap-4 px-5 py-4 rounded-2xl text-lg font-semibold transition-all
                      {{ request()->routeIs('inventario.*') ? 'bg-purple-100 text-purple-800' : 'text-gray-700 hover:bg-purple-50' }}">
                <span class="text-2xl">📦</span>
                <span>Inventario</span>
            </a>

            {{-- Finanzas --}}
            <a href="{{ route('finanzas.index') }}"
               class="flex items-center gap-4 px-5 py-4 rounded-2xl text-lg font-semibold transition-all
                      {{ request()->routeIs('finanzas.*') ? 'bg-purple-100 text-purple-800' : 'text-gray-700 hover:bg-purple-50' }}">
                <span class="text-2xl">💰</span>
                <span>Finanzas</span>
            </a>

            {{-- Reportes --}}
            <div class="space-y-1">
                <div class="px-5 py-3 text-sm font-bold text-gray-500 uppercase">📊 Reportes</div>
                <a href="{{ route('reportes.ventas.index') }}"
                   class="flex items-center gap-4 px-8 py-3 rounded-2xl text-base font-medium transition-all
                          {{ request()->routeIs('reportes.ventas.*') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-blue-50' }}">
                    <span>📈 Ventas</span>
                </a>
                <a href="{{ route('reportes.cxc') }}"
                   class="flex items-center gap-4 px-8 py-3 rounded-2xl text-base font-medium transition-all
                          {{ request()->routeIs('reportes.cxc') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-blue-50' }}">
                    <span>💳 Créditos</span>
                </a>
            </div>

            {{-- Más opciones --}}
            <div class="space-y-1 pt-2">
                <div class="px-5 py-3 text-sm font-bold text-gray-500 uppercase">⚙️ Más</div>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-4 px-8 py-3 rounded-2xl text-base font-medium transition-all
                          {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-blue-50' }}">
                    <span>🏠 Dashboard</span>
                </a>
                <a href="{{ route('proveedores.index') }}"
                   class="flex items-center gap-4 px-8 py-3 rounded-2xl text-base font-medium transition-all
                          {{ request()->routeIs('proveedores.*') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-blue-50' }}">
                    <span>🚚 Proveedores</span>
                </a>
                <a href="{{ route('cash-movements.index') }}"
                   class="flex items-center gap-4 px-8 py-3 rounded-2xl text-base font-medium transition-all
                          {{ request()->routeIs('cash-movements.*') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-blue-50' }}">
                    <span>💵 Movimientos de Caja</span>
                </a>
                <a href="{{ route('ingresos.index') }}"
                   class="flex items-center gap-4 px-8 py-3 rounded-2xl text-base font-medium transition-all
                          {{ request()->routeIs('ingresos.*') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-blue-50' }}">
                    <span>📦 Movimientos Inventario</span>
                </a>
            </div>

        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-5 border-t-2 border-purple-200 bg-gradient-to-r from-purple-50 to-pink-50">
            <!-- Usuario actual (móvil) - MÁS GRANDE -->
            <div class="px-4 mb-4">
                <div class="flex items-center gap-4 p-5 bg-gradient-to-r from-green-100 to-emerald-100 rounded-2xl border-2 border-green-300 shadow-md">
                    <span class="text-4xl">{{ ['MAMI' => '👩', 'PAPI' => '👨', 'NATALY' => '👧', 'OTROS' => '👤'][Auth::user()->name] ?? '👤' }}</span>
                    <div>
                        <div class="font-bold text-gray-800 text-xl">{{ Auth::user()->name }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>

            <!-- Botón Cerrar Sesión (móvil) - MÁS GRANDE -->
            <div class="px-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-3 px-6 py-5 bg-gradient-to-r from-rose-300 to-pink-300 hover:from-rose-400 hover:to-pink-400 text-gray-800 font-bold text-xl rounded-2xl shadow-lg transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
