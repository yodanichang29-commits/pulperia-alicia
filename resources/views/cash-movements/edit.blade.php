<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">✏️ Editar Movimiento de Caja</h2>
            <a href="{{ route('cash-movements.show', $movement) }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-xl font-semibold transition">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                
                <form action="{{ route('cash-movements.update', $movement) }}" method="POST" enctype="multipart/form-data" x-data="cashMovementForm()">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-6">

                        {{-- TIPO DE MOVIMIENTO --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">🔄 Tipo de Movimiento *</label>
                            <div class="grid grid-cols-2 gap-4">
                                {{-- Ingreso --}}
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="ingreso" 
                                           x-model="type"
                                           class="peer sr-only" 
                                           {{ old('type', $movement->type) === 'ingreso' ? 'checked' : '' }}>
                                    <div class="border-2 rounded-xl p-4 text-center transition
                                                peer-checked:border-emerald-500 peer-checked:bg-emerald-50
                                                hover:border-emerald-300">
                                        <div class="text-3xl mb-2">✅</div>
                                        <div class="font-semibold text-gray-800">Ingreso</div>
                                        <div class="text-xs text-gray-500 mt-1">Entrada de dinero</div>
                                    </div>
                                </label>

                                {{-- Egreso --}}
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="egreso" 
                                           x-model="type"
                                           class="peer sr-only"
                                           {{ old('type', $movement->type) === 'egreso' ? 'checked' : '' }}>
                                    <div class="border-2 rounded-xl p-4 text-center transition
                                                peer-checked:border-red-500 peer-checked:bg-red-50
                                                hover:border-red-300">
                                        <div class="text-3xl mb-2">❌</div>
                                        <div class="font-semibold text-gray-800">Egreso</div>
                                        <div class="text-xs text-gray-500 mt-1">Salida de dinero</div>
                                    </div>
                                </label>
                            </div>
                            @error('type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- FECHA --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">📅 Fecha *</label>
                            <input type="date" name="date" 
                                   value="{{ old('date', $movement->date->format('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   required>
                            @error('date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- CATEGORÍA --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">🏷️ Categoría *</label>
                            <select name="category" 
                                    x-model="category"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    required>
                                <option value="">-- Seleccionar categoría --</option>
                                
                                <template x-if="type === 'ingreso'">
                                    <optgroup label="Categorías de Ingresos">
                                        @foreach($categoriesIngreso as $cat)
                                            <option value="{{ $cat }}" 
                                                {{ old('category', $movement->category ?? 'Otro') === $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </template>

                                <template x-if="type === 'egreso'">
                                    <optgroup label="Categorías de Egresos">
                                        @foreach($categoriesEgreso as $cat)
                                            <option value="{{ $cat }}" 
                                                {{ old('category', $movement->category ?? 'Otro') === $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </template>
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- CATEGORÍA PERSONALIZADA (si eligió "Otro") --}}
                        <div x-show="category === 'Otro'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">✍️ Especificar categoría *</label>
                            <input type="text" name="custom_category" 
                                   value="{{ old('custom_category', $movement->custom_category) }}"
                                   placeholder="Escriba la categoría personalizada"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   maxlength="255">
                            @error('custom_category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- DESCRIPCIÓN --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">📝 Descripción *</label>
                            <textarea name="description" rows="3"
                                      placeholder='Ej: "Recibo de luz de octubre 2025"'
                                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                      maxlength="500"
                                      required>{{ old('description', $movement->description) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Máximo 500 caracteres</p>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- MONTO --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">💵 Monto (L) *</label>
                            <input type="number" name="amount" 
                                   value="{{ old('amount', $movement->amount) }}"
                                   step="0.01" min="0.01"
                                   placeholder="0.00"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   required>
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- MÉTODO DE PAGO --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">💳 Método de pago *</label>
                            <select name="payment_method"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    required>
                                <option value="">-- Seleccionar método --</option>
                                <option value="efectivo" {{ old('payment_method', $movement->payment_method) === 'efectivo' ? 'selected' : '' }}>
                                    💵 Efectivo
                                </option>
                                <option value="transferencia" {{ old('payment_method', $movement->payment_method) === 'transferencia' ? 'selected' : '' }}>
                                    🏦 Transferencia bancaria
                                </option>
                                <option value="tarjeta" {{ old('payment_method', $movement->payment_method) === 'tarjeta' ? 'selected' : '' }}>
                                    💳 Tarjeta
                                </option>
                                <option value="otro" {{ old('payment_method', $movement->payment_method) === 'otro' ? 'selected' : '' }}>
                                    ➕ Otro
                                </option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- COMPROBANTE ACTUAL --}}
                        @if($movement->receipt_file)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">📎 Comprobante actual</label>
                            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ basename($movement->receipt_file) }}</p>
                                </div>
                                <a href="{{ $movement->receipt_url }}" target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Ver archivo
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- NUEVO COMPROBANTE (opcional) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                📎 {{ $movement->receipt_file ? 'Reemplazar comprobante (opcional)' : 'Comprobante (opcional)' }}
                            </label>
                            <input type="file" name="receipt_file" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <p class="mt-1 text-xs text-gray-500">
                                @if($movement->receipt_file)
                                    Si subes un nuevo archivo, reemplazará al actual.
                                @else
                                    Archivos permitidos: PDF, JPG, JPEG, PNG (máximo 5MB)
                                @endif
                            </p>
                            @error('receipt_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NOTAS (opcional) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">📋 Notas adicionales (opcional)</label>
                            <textarea name="notes" rows="3"
                                      placeholder="Cualquier información adicional relevante..."
                                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                      maxlength="1000">{{ old('notes', $movement->notes) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Máximo 1000 caracteres</p>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- BOTONES --}}
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t">
                        <a href="{{ route('cash-movements.show', $movement) }}" 
                           class="px-6 py-3 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 font-semibold transition">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-6 py-3 rounded-xl text-white font-semibold transition
                                       bg-blue-600 hover:bg-blue-700">
                            💾 Guardar Cambios
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function cashMovementForm() {
            return {
                type: '{{ old('type', $movement->type) }}',
                category: '{{ old('category', $movement->category ?? 'Otro') }}',
                
                init() {
                    // Inicializar con los valores actuales
                    console.log('Tipo:', this.type);
                    console.log('Categoría:', this.category);
                }
            }
        }
    </script>
    @endpush

</x-app-layout>