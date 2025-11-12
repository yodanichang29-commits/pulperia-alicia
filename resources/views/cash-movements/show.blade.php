<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">💰 Detalle del Movimiento</h2>
            <a href="{{ route('cash-movements.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-xl font-semibold transition">
                ← Volver a la lista
            </a>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-4xl mx-auto">

            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                
                {{-- ENCABEZADO DEL MOVIMIENTO --}}
                <div class="p-6 border-b {{ $movement->isIngreso() ? 'bg-emerald-50' : 'bg-red-50' }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-4xl">{{ $movement->type_icon }}</span>
                                <div>
                                    <h3 class="text-2xl font-bold {{ $movement->isIngreso() ? 'text-emerald-800' : 'text-red-800' }}">
                                        {{ $movement->type_label }}
                                    </h3>
                                    <p class="text-sm text-gray-600">{{ $movement->final_category }}</p>
                                </div>
                            </div>
                            <p class="text-4xl font-black {{ $movement->isIngreso() ? 'text-emerald-700' : 'text-red-700' }} mt-2">
                                L {{ number_format($movement->amount, 2) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Fecha</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $movement->date->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $movement->date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
                        </div>
                    </div>
                </div>

                {{-- INFORMACIÓN DETALLADA --}}
                <div class="p-6 space-y-6">

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">📝 Descripción</label>
                        <p class="text-gray-900 text-lg">{{ $movement->description }}</p>
                    </div>

                    {{-- Grid de información --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Tipo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">🔄 Tipo</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $movement->isIngreso() ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $movement->type_icon }} {{ $movement->type_label }}
                            </span>
                        </div>

                        {{-- Categoría --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">🏷️ Categoría</label>
                            <p class="text-gray-900 font-medium">{{ $movement->final_category }}</p>
                        </div>

                        {{-- Monto --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">💵 Monto</label>
                            <p class="text-2xl font-bold {{ $movement->isIngreso() ? 'text-emerald-700' : 'text-red-700' }}">
                                L {{ number_format($movement->amount, 2) }}
                            </p>
                        </div>

                        {{-- Método de pago --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">💳 Método de pago</label>
                            <p class="text-gray-900 font-medium">{{ $movement->payment_method_label }}</p>
                        </div>

                    </div>

                    {{-- Comprobante --}}
                    @if($movement->receipt_file)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">📎 Comprobante</label>
                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Archivo adjunto</p>
                                <p class="text-xs text-gray-500">{{ basename($movement->receipt_file) }}</p>
                            </div>
                            <a href="{{ $movement->receipt_url }}" target="_blank" 
                               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                                Ver archivo
                            </a>
                            <a href="{{ $movement->receipt_url }}" download
                               class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition">
                                Descargar
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Notas --}}
                    @if($movement->notes)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">📋 Notas adicionales</label>
                        <div class="p-4 bg-gray-50 rounded-lg border">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $movement->notes }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Información de auditoría --}}
                    <div class="border-t pt-6">
                        <label class="block text-sm font-medium text-gray-500 mb-3">ℹ️ Información de registro</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Registrado por:</p>
                                <p class="text-gray-900 font-medium">
                                    {{ $movement->creator ? $movement->creator->name : 'Usuario desconocido' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500">Fecha de registro:</p>
                                <p class="text-gray-900 font-medium">
                                       {{ $movement->created_at->format('d/m/Y h:i:s A') }}

                                </p>
                            </div>
                            @if($movement->updated_at != $movement->created_at)
                            <div>
                                <p class="text-gray-500">Última modificación:</p>
                                <p class="text-gray-900 font-medium">
                                    {{ $movement->updated_at->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                            @endif
                            <div>
                                <p class="text-gray-500">ID del movimiento:</p>
                                <p class="text-gray-900 font-mono">#{{ $movement->id }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- BOTONES DE ACCIÓN --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t">
                    <button type="button"
                            onclick="if(confirm('¿Estás seguro de eliminar este movimiento? Esta acción no se puede deshacer.')) { document.getElementById('delete-form').submit(); }"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                        🗑️ Eliminar
                    </button>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('cash-movements.index') }}" 
                           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition">
                            Volver
                        </a>
                        <a href="{{ route('cash-movements.edit', $movement) }}" 
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                            ✏️ Editar
                        </a>
                    </div>
                </div>

            </div>

            {{-- Formulario oculto para eliminar --}}
            <form id="delete-form" 
                  action="{{ route('cash-movements.destroy', $movement) }}" 
                  method="POST" 
                  class="hidden">
                @csrf
                @method('DELETE')
            </form>

        </div>
    </div>

</x-app-layout>
