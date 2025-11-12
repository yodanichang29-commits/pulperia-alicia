<nav x-data="{ open: false }"
     class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">


    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo (si tienes uno, colócalo aquí) -->
                {{-- <div class="shrink-0 flex items-center">
                    <a href="{{ route('caja') }}">
                        <img src="{{ asset('brand/favicon-512.png') }}" class="h-8 w-8" alt="logo">
                    </a>
                </div> --}}

                <!-- Navigation Links (DESKTOP) -->
                <div class="hidden space-x-2 sm:-my-px sm:ms-10 sm:flex">

{{-- Dashboard --}}
<x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" class="rounded-xl">
    <span class="inline-flex items-center gap-2">
        <!-- Heroicon: chart-bar -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 3v18h18M9 17v-6m4 6V9m4 8V5"/>
        </svg>
        <span>Dashboard</span>
    </span>
</x-nav-link>



                    {{-- Caja --}}
                    <x-nav-link :href="route('caja')" :active="request()->routeIs('caja')" class="rounded-xl">
                        <span class="inline-flex items-center gap-2">
                          <!-- Heroicon: cart -->
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                               fill="none" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M2.25 3h1.386c.51 0 .955.343 1.091.835l.383 1.44m0 0L6.75 12.75m-1.64-7.475h13.78a.75.75 0 01.73.93l-1.28 5.12a1.5 1.5 0 01-1.46 1.15H7.17m0 0l-.42-1.68M7.5 20.25a.75.75 0 100-1.5.75.75 0 000 1.5zm9 0a.75.75 0 100-1.5.75.75 0 000 1.5z"/>
                          </svg>
                          <span>Caja</span>
                        </span>
                    </x-nav-link>

                    {{-- Reporte CxC --}}
                    <x-nav-link :href="route('reportes.cxc')" :active="request()->routeIs('reportes.cxc')" class="rounded-xl">
                        <span class="inline-flex items-center gap-2">
                          <!-- Heroicon: banknotes -->
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                               fill="none" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M2.25 7.5h19.5m-19.5 0A2.25 2.25 0 014.5 5.25h15a2.25 2.25 0 012.25 2.25m-19.5 0v9a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25v-9m-3 0a3 3 0 01-3 3H8.25a3 3 0 01-3-3"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M12 10.5c1.657 0 3 1.12 3 2.5s-1.343 2.5-3 2.5-3-1.12-3-2.5 1.343-2.5 3-2.5z"/>
                          </svg>
                          <span>Reporte Credito</span>
                        </span>
                    </x-nav-link>

{{-- Inventario --}}
<x-nav-link :href="route('inventario.index')" :active="request()->routeIs('inventario.*')" class="rounded-xl">
    <span class="inline-flex items-center gap-2">
        <!-- Icono tipo caja (outline) -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" class="w-5 h-5" stroke-width="1.8">
            <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
            <path d="M3 10h18"></path>
            <path d="M9 16h6"></path>
        </svg>
        <span>Inventario</span>
    </span>
</x-nav-link>




{{-- Ingresos y Egresos --}}
<x-nav-link href="{{ route('ingresos.index') }}" :active="request()->routeIs('ingresos.*')" class="rounded-md">
    <span class="inline-flex items-center gap-2">
        <!-- Icono tipo caja o movimiento -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 3v1a2 2 0 002 2h2.28a2 2 0 011.42.59l1.42 1.42a2 2 0 001.42.59h5.46a2 2 0 001.42-.59l1.42-1.42A2 2 0 0121 6h0a2 2 0 002 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V3z" />
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 8v8m4-4H8" />
        </svg>
        <span>Ingresos y Egresos Mercancia</span>
    </span>
</x-nav-link>



{{-- Proveedores --}}
<a href="{{ route('proveedores.index') }}"
   class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md
          {{ request()->routeIs('proveedores.*') ? 'text-blue-700 border-b-2 border-blue-600' : 'text-gray-700 hover:text-gray-900' }}">
  {{-- Icono cajita --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 me-2" viewBox="0 0 24 24" fill="currentColor">
    <path d="M21 8.5V18a2 2 0 0 1-1.2 1.82l-6.8 3.01a2 2 0 0 1-1.6 0L3.6 19.82A2 2 0 0 1 2.4 18V8.5l9 3.99 9-3.99zM12 2l9 4.02-9 3.98L3 6.02 12 2z"/>
  </svg>
  Proveedores
</a>





{{-- Movimientos de Caja --}}
<x-nav-link href="{{ route('cash-movements.index') }}"
            :active="request()->routeIs('cash-movements.*')"
            class="rounded-xl">
    <span class="inline-flex items-center gap-2">
        <!-- Ícono: billete con flechas -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" 
             stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" 
                  d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
        </svg>
        <span>Movimientos de Caja</span>
    </span>
</x-nav-link>






{{-- Reporte Ventas --}}
<x-nav-link href="{{ route('reportes.ventas.index') }}"
            :active="request()->routeIs('reportes.ventas.*')"
            class="rounded-xl">    <span class="inline-flex items-center gap-2">
        <!-- Heroicon: chart-bar -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
             class="w-5 h-5" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3v18h18"/>
            <path d="M9 17V9M13 17v-6M17 17v-3"/>
        </svg>
        <span>Reporte Ventas</span>
    </span>
</x-nav-link>



{{-- Finanzas --}}
<x-nav-link href="{{ route('finanzas.index') }}" :active="request()->routeIs('finanzas.*')" class="rounded-md">
    <span class="inline-flex items-center gap-2">
        <!-- Icono tipo gráfico o balance -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" 
                  d="M3 3v18h18M7 13l3-3 3 3 4-4m-7 7h7" />
        </svg>
        <span>Finanzas</span>
    </span>
</x-nav-link>



                </div>
            </div>

            <!-- Settings Dropdown (desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <!-- Usuario actual -->
                <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                    <span class="text-2xl">{{ ['MAMI' => '👩', 'PAPI' => '👨', 'NATALY' => '👸', 'OTROS' => '👤'][Auth::user()->name] ?? '👤' }}</span>
                    <span class="font-semibold text-gray-700">{{ Auth::user()->name }}</span>
                </div>

                <!-- Botón Cerrar Sesión prominente -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (MÓVIL) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">

            <!-- Caja -->
            <x-responsive-nav-link :href="route('caja')" :active="request()->routeIs('caja')">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M2.25 3h1.386c.51 0 .955.343 1.091.835l.383 1.44m0 0L6.75 12.75m-1.64-7.475h13.78a.75.75 0 01.73.93l-1.28 5.12a1.5 1.5 0 01-1.46 1.15H7.17m0 0l-.42-1.68M7.5 20.25a.75.75 0 100-1.5.75.75 0 000 1.5zm9 0a.75.75 0 100-1.5.75.75 0 000 1.5z"/>
                    </svg>
                    <span>Caja</span>
                </span>
            </x-responsive-nav-link>

            <!-- Reporte CxC -->
            <x-responsive-nav-link :href="route('reportes.cxc')" :active="request()->routeIs('reportes.cxc')">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M2.25 7.5h19.5m-19.5 0A2.25 2.25 0 014.5 5.25h15a2.25 2.25 0 012.25 2.25m-19.5 0v9a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25v-9m-3 0a3 3 0 01-3 3H8.25a3 3 0 01-3-3"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 10.5c1.657 0 3 1.12 3 2.5s-1.343 2.5-3 2.5-3-1.12-3-2.5 1.343-2.5 3-2.5z"/>
                    </svg>
                    <span>Reporte Credito</span>
                </span>
            </x-responsive-nav-link>



            {{-- Proveedores (móvil) --}}
<a href="{{ route('proveedores.index') }}"
   class="block px-4 py-2 text-base {{ request()->routeIs('proveedores.*') ? 'text-blue-700 font-semibold' : 'text-gray-700' }}">
  Proveedores
</a>


        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-4 border-t border-gray-200">
            <!-- Usuario actual (móvil) -->
            <div class="px-4 mb-4">
                <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                    <span class="text-3xl">{{ ['MAMI' => '👩', 'PAPI' => '👨', 'NATALY' => '👸', 'OTROS' => '👤'][Auth::user()->name] ?? '👤' }}</span>
                    <div>
                        <div class="font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>

            <!-- Botón Cerrar Sesión prominente (móvil) -->
            <div class="px-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
