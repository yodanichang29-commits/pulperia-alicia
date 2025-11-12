<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">💰 Movimientos de Caja</h2>
            <div class="flex gap-2">
                <a href="{{ route('cash-movements.create') }}?type=ingreso" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-semibold transition">
                    ✅ Nuevo Ingreso
                </a>
                <a href="{{ route('cash-movements.create') }}?type=egreso" 
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl font-semibold transition">
                    ❌ Nuevo Egreso
                </a>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- Mensaje de éxito --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        {{-- FILTROS --}}
        <form method="GET" class="bg-white p-4 rounded-xl shadow">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                {{-- Fecha inicio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="start" value="{{ $filters['start'] }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>

                {{-- Fecha fin --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="end" value="{{ $filters['end'] }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>

                {{-- Tipo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Todos</option>
                        <option value="ingreso" {{ $filters['type'] === 'ingreso' ? 'selected' : '' }}>✅ Ingresos</option>
                        <option value="egreso" {{ $filters['type'] === 'egreso' ? 'selected' : '' }}>❌ Egresos</option>
                    </select>
                </div>

                {{-- Categoría --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select name="category" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Todas</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat }}" {{ $filters['category'] === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Método de pago --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Método</label>
                    <select name="payment_method" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Todos</option>
                        <option value="efectivo" {{ $filters['payment_method'] === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="transferencia" {{ $filters['payment_method'] === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                        <option value="tarjeta" {{ $filters['payment_method'] === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                        <option value="otro" {{ $filters['payment_method'] === 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                    Filtrar
                </button>
                <a href="{{ route('cash-movements.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold transition">
                    Limpiar
                </a>
            </div>
        </form>

        {{-- RESUMEN DEL PERÍODO --}}
        <section class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-lg font-semibold mb-4">📊 Resumen del Período</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Total Ingresos --}}
                <div class="rounded-xl border-2 border-emerald-200 bg-emerald-50 p-6">
                    <p class="text-sm text-emerald-800 font-medium mb-1">✅ Total Ingresos</p>
                    <p class="text-4xl font-black text-emerald-700">L {{ number_format($totalIngresos, 2) }}</p>
                </div>

                {{-- Total Egresos --}}
                <div class="rounded-xl border-2 border-red-200 bg-red-50 p-6">
                    <p class="text-sm text-red-800 font-medium mb-1">❌ Total Egresos</p>
                    <p class="text-4xl font-black text-red-700">L {{ number_format($totalEgresos, 2) }}</p>
                </div>
            </div>
        </section>

        {{-- INGRESOS POR CATEGORÍA --}}
        @if($ingresosPorCategoria->isNotEmpty())
        <section class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-lg font-semibold mb-4">📈 Ingresos del Mes (no incluye ventas)</h3>
            <div class="space-y-3">
                @foreach($ingresosPorCategoria as $cat)
                    @php
                        $percentage = $totalIngresos > 0 ? ($cat->total / $totalIngresos) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $cat->cat }}</span>
                            <span class="text-sm font-bold text-emerald-700">L {{ number_format($cat->total, 2) }} ({{ number_format($percentage, 1) }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
                <div class="border-t pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-800">TOTAL INGRESOS:</span>
                        <span class="font-bold text-emerald-700 text-lg">L {{ number_format($totalIngresos, 2) }}</span>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- EGRESOS POR CATEGORÍA --}}
        @if($egresosPorCategoria->isNotEmpty())
        <section class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-lg font-semibold mb-4">📉 Egresos del Mes</h3>
            <div class="space-y-3">
                @foreach($egresosPorCategoria as $cat)
                    @php
                        $percentage = $totalEgresos > 0 ? ($cat->total / $totalEgresos) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $cat->cat }}</span>
                            <span class="text-sm font-bold text-red-700">L {{ number_format($cat->total, 2) }} ({{ number_format($percentage, 1) }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
                <div class="border-t pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-800">TOTAL EGRESOS:</span>
                        <span class="font-bold text-red-700 text-lg">L {{ number_format($totalEgresos, 2) }}</span>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- COMPARACIÓN MES ANTERIOR --}}
        <section class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-lg font-semibold mb-4">📊 Comparación: Este Mes vs Mes Anterior</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Comparación Ingresos --}}
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-2">Ingresos</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-emerald-700">L {{ number_format($totalIngresos, 2) }}</span>
                        <span class="text-sm text-gray-500">Este mes</span>
                    </div>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-lg text-gray-600">L {{ number_format($prevIngresos, 2) }}</span>
                        <span class="text-sm text-gray-500">Mes anterior</span>
                    </div>
                    @if($ingresosChange != 0)
                        <div class="mt-2">
                            <span class="text-sm font-semibold {{ $ingresosChange > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $ingresosChange > 0 ? '↑' : '↓' }} {{ number_format(abs($ingresosChange), 1) }}%
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Comparación Egresos --}}
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-2">Egresos</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-red-700">L {{ number_format($totalEgresos, 2) }}</span>
                        <span class="text-sm text-gray-500">Este mes</span>
                    </div>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-lg text-gray-600">L {{ number_format($prevEgresos, 2) }}</span>
                        <span class="text-sm text-gray-500">Mes anterior</span>
                    </div>
                    @if($egresosChange != 0)
                        <div class="mt-2">
                            <span class="text-sm font-semibold {{ $egresosChange > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $egresosChange > 0 ? '↑' : '↓' }} {{ number_format(abs($egresosChange), 1) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- LISTADO DETALLADO --}}
        <section class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold">📋 Listado Detallado</h3>
            </div>

            @if($movements->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    No hay movimientos registrados en este período.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($movements as $movement)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $movement->date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $movement->isIngreso() ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $movement->type_icon }} {{ $movement->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $movement->final_category }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ Str::limit($movement->description, 40) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $movement->payment_method_label }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold
                                        {{ $movement->isIngreso() ? 'text-emerald-700' : 'text-red-700' }}">
                                        L {{ number_format($movement->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <a href="{{ route('cash-movements.show', $movement) }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="p-4 border-t">
                    {{ $movements->links() }}
                </div>
            @endif
        </section>

    </div>
</x-app-layout>