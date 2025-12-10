<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Encabezado --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        💵 Movimientos de Caja
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Solo efectivo físico de la gaveta
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('cash-movements.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        ✅ Nuevo Movimiento
                    </a>
                </div>
            </div>

            {{-- Mensajes de éxito --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Filtros --}}
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('cash-movements.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        {{-- Desde --}}
                        <div>
                            <label for="start" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                            <input type="date" 
                                   name="start" 
                                   id="start"
                                   value="{{ $filters['start'] }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        {{-- Hasta --}}
                        <div>
                            <label for="end" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <input type="date" 
                                   name="end" 
                                   id="end"
                                   value="{{ $filters['end'] }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        {{-- Tipo --}}
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="type" 
                                    id="type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="ingreso" {{ $filters['type'] === 'ingreso' ? 'selected' : '' }}>🟢 Ingresos</option>
                                <option value="egreso" {{ $filters['type'] === 'egreso' ? 'selected' : '' }}>🔴 Egresos</option>
                            </select>
                        </div>

                        {{-- Categoría --}}
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <select name="category" 
                                    id="category"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todas</option>
                                @foreach($allCategories as $cat)
                                    <option value="{{ $cat }}" {{ $filters['category'] === $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Botones --}}
                        <div class="flex items-end space-x-2">
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Filtrar
                            </button>
                            <a href="{{ route('cash-movements.index') }}" 
                               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Resumen del Período --}}
            <div class="grid grid-cols-2 gap-6 mb-6">
                {{-- Total Ingresos --}}
                <div class="bg-green-50 border-l-4 border-green-400 p-6 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-800 mb-1">✅ Total Ingresos</p>
                            <p class="text-3xl font-bold text-green-900">L {{ number_format($totalIngresos, 2) }}</p>
                        </div>
                        <div class="text-5xl opacity-20">🟢</div>
                    </div>
                </div>

                {{-- Total Egresos --}}
                <div class="bg-red-50 border-l-4 border-red-400 p-6 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-800 mb-1">❌ Total Egresos</p>
                            <p class="text-3xl font-bold text-red-900">L {{ number_format($totalEgresos, 2) }}</p>
                        </div>
                        <div class="text-5xl opacity-20">🔴</div>
                    </div>
                </div>
            </div>

            {{-- Tabla de movimientos --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                @if($movements->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($movements as $movement)
                                <tr class="hover:bg-gray-50">
                                    {{-- Fecha --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $movement->date->format('d/m/Y') }}
                                    </td>

                                    {{-- Tipo --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($movement->type === 'ingreso')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                🟢 Ingreso
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                🔴 Egreso
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Categoría --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($movement->type === 'ingreso')
                                            <span class="text-green-700 font-medium">Fondo Inicial</span>
                                        @else
                                            {{ $movement->custom_category ?? $movement->category }}
                                        @endif
                                    </td>

                                    {{-- Descripción --}}
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ Str::limit($movement->description, 50) }}
                                    </td>

                                    {{-- Monto --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($movement->type === 'ingreso')
                                            <span class="text-green-600">+L {{ number_format($movement->amount, 2) }}</span>
                                        @else
                                            <span class="text-red-600">-L {{ number_format($movement->amount, 2) }}</span>
                                        @endif
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('cash-movements.show', $movement) }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">
                                            Ver
                                        </a>
                                        <a href="{{ route('cash-movements.edit', $movement) }}" 
                                           class="text-yellow-600 hover:text-yellow-900">
                                           Editar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Paginación --}}
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $movements->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-500 text-lg">No hay movimientos registrados</p>
                        <p class="text-gray-400 text-sm mt-2">Comienza registrando tu primer movimiento de caja</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>