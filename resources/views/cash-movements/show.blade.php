<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Encabezado --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Detalle del Movimiento
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Movimiento #{{ $movement->id }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('cash-movements.edit', $movement) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                        ✏️ Editar
                    </a>
                    <a href="{{ route('cash-movements.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Volver
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

            {{-- Contenido --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                
                {{-- Tipo de movimiento (destacado) --}}
                <div class="px-6 py-8 {{ $movement->type === 'ingreso' ? 'bg-green-50 border-l-8 border-green-500' : 'bg-red-50 border-l-8 border-red-500' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            @if($movement->type === 'ingreso')
                                <h2 class="text-2xl font-bold text-green-900">🟢 Ingreso al Fondo</h2>
                                <p class="mt-1 text-sm text-green-700">Efectivo agregado a la gaveta</p>
                            @else
                                <h2 class="text-2xl font-bold text-red-900">🔴 Salida de Efectivo</h2>
                                <p class="mt-1 text-sm text-red-700">Gasto pagado desde la caja</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold {{ $movement->type === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $movement->type === 'ingreso' ? '+' : '-' }}L {{ number_format($movement->amount, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Detalles --}}
                <div class="px-6 py-6">
                    <dl class="grid grid-cols-1 gap-6">
                        
                        {{-- Fecha --}}
                        <div class="border-b border-gray-200 pb-4">
                            <dt class="text-sm font-medium text-gray-500 mb-1">📅 Fecha</dt>
                            <dd class="text-lg text-gray-900">{{ $movement->date->format('d/m/Y') }}</dd>
                        </div>

                        {{-- Categoría (solo para egresos) --}}
                        @if($movement->type === 'egreso')
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="text-sm font-medium text-gray-500 mb-1">📂 Categoría</dt>
                                <dd class="text-lg text-gray-900">
                                    {{ $movement->custom_category ?? $movement->category }}
                                </dd>
                            </div>
                        @endif

                        {{-- Descripción --}}
                        <div class="border-b border-gray-200 pb-4">
                            <dt class="text-sm font-medium text-gray-500 mb-1">📝 Descripción</dt>
                            <dd class="text-lg text-gray-900">{{ $movement->description }}</dd>
                        </div>

                        {{-- Método de pago --}}
                        <div class="border-b border-gray-200 pb-4">
                            <dt class="text-sm font-medium text-gray-500 mb-1">💵 Método de pago</dt>
                            <dd class="text-lg text-gray-900">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Efectivo
                                </span>
                            </dd>
                        </div>

                        {{-- Turno asociado --}}
                        @if($movement->cashShift)
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="text-sm font-medium text-gray-500 mb-1">🔄 Turno</dt>
                                <dd class="text-lg text-gray-900">
                                    Turno #{{ $movement->cashShift->id }} 
                                    <span class="text-sm text-gray-500">
                                        ({{ $movement->cashShift->opened_at->format('d/m/Y H:i') }})
                                    </span>
                                </dd>
                            </div>
                        @endif

                        {{-- Comprobante --}}
                        @if($movement->receipt_file)
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="text-sm font-medium text-gray-500 mb-1">📎 Comprobante</dt>
                                <dd class="text-lg">
                                    <a href="{{ Storage::url($movement->receipt_file) }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        📄 Ver archivo
                                    </a>
                                </dd>
                            </div>
                        @endif

                        {{-- Notas --}}
                        @if($movement->notes)
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="text-sm font-medium text-gray-500 mb-1">📌 Notas</dt>
                                <dd class="text-lg text-gray-900">{{ $movement->notes }}</dd>
                            </div>
                        @endif

                        {{-- Creado por --}}
                        <div class="border-b border-gray-200 pb-4">
                            <dt class="text-sm font-medium text-gray-500 mb-1">👤 Registrado por</dt>
                            <dd class="text-lg text-gray-900">
                                {{ $movement->creator->name ?? 'Usuario desconocido' }}
                                <span class="text-sm text-gray-500">
                                    el {{ $movement->created_at->format('d/m/Y H:i') }}
                                </span>
                            </dd>
                        </div>

                    </dl>
                </div>

                {{-- Botón de eliminar --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <form action="{{ route('cash-movements.destroy', $movement) }}" 
                          method="POST"
                          onsubmit="return confirm('¿Estás seguro de eliminar este movimiento?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            🗑️ Eliminar Movimiento
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>