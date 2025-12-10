<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Encabezado --}}
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">
                    💵 Nuevo Movimiento de Caja
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Solo efectivo físico de la gaveta. Debes tener un turno abierto.
                </p>
            </div>

            {{-- Errores --}}
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Errores:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Formulario --}}
            <form action="{{ route('cash-movements.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
                @csrf

                {{-- Tipo de movimiento --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Tipo de movimiento <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        {{-- INGRESO = Agregar al fondo --}}
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-4 hover:border-green-500 focus:outline-none transition-all duration-200" id="label-ingreso">
                            <input type="radio" name="type" value="ingreso" 
                                   class="sr-only" 
                                   {{ old('type') === 'ingreso' ? 'checked' : '' }}
                                   onchange="toggleCategories('ingreso')">
                            <span class="flex flex-1">
                                <span class="flex flex-col">
                                    <span class="block text-lg font-medium text-gray-900">
                                        🟢 Agregar al Fondo
                                    </span>
                                    <span class="mt-1 flex items-center text-sm text-gray-500">
                                        Meter efectivo a la gaveta
                                    </span>
                                </span>
                            </span>
                        </label>

                        {{-- EGRESO = Sacar efectivo --}}
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-4 hover:border-red-500 focus:outline-none transition-all duration-200" id="label-egreso">
                            <input type="radio" name="type" value="egreso" 
                                   class="sr-only" 
                                   {{ old('type') === 'egreso' ? 'checked' : '' }}
                                   onchange="toggleCategories('egreso')">
                            <span class="flex flex-1">
                                <span class="flex flex-col">
                                    <span class="block text-lg font-medium text-gray-900">
                                        🔴 Sacar Efectivo
                                    </span>
                                    <span class="mt-1 flex items-center text-sm text-gray-500">
                                        Pagar gastos en efectivo
                                    </span>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Fecha --}}
                <div class="mb-6">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="date" 
                           id="date"
                           value="{{ old('date', now()->format('Y-m-d')) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                {{-- Categorías (solo para EGRESOS) --}}
                <div id="category-section" style="display: none;">
                    <div class="mb-6">
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            Categoría <span class="text-red-500">*</span>
                        </label>
                        <select name="category" 
                                id="category"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                onchange="toggleCustomCategory()">
                            <option value="">Seleccionar...</option>
                            @foreach([
                                'Agua',
                                'Luz',
                                'Alquiler',
                                'Salarios',
                                'Internet',
                                'Gasolina',
                                'Comida',
                                'Medicamentos',
                                'Consultas',
                                'Otro'
                            ] as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Categoría personalizada (solo si eligió "Otro") --}}
                    <div id="custom-category-section" style="display: none;">
                        <div class="mb-6">
                            <label for="custom_category" class="block text-sm font-medium text-gray-700 mb-2">
                                Especificar categoría
                            </label>
                            <input type="text" 
                                   name="custom_category" 
                                   id="custom_category"
                                   value="{{ old('custom_category') }}"
                                   placeholder="Escribe la categoría"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

          {{-- Descripción (OPCIONAL para ambos) --}}
<div class="mb-6">
    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
        Descripción 
        <span class="text-gray-500 text-xs">(Opcional - si no la llenas, se usará la categoría)</span>
    </label>
    <textarea name="description" 
              id="description"
              rows="3"
              placeholder="Opcional: agrega detalles adicionales..."
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
</div>

                {{-- Monto --}}
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Monto (L) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="amount" 
                           id="amount"
                           step="0.01"
                           min="0.01"
                           value="{{ old('amount') }}"
                           placeholder="0.00"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                {{-- Comprobante --}}
                <div class="mb-6">
                    <label for="receipt_file" class="block text-sm font-medium text-gray-700 mb-2">
                        Comprobante (opcional)
                    </label>
                    <input type="file" 
                           name="receipt_file" 
                           id="receipt_file"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">
                        PDF, JPG, PNG (máximo 5MB)
                    </p>
                </div>

                {{-- Notas --}}
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notas adicionales
                    </label>
                    <textarea name="notes" 
                              id="notes"
                              rows="2"
                              placeholder="Notas opcionales..."
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('cash-movements.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        💾 Guardar Movimiento
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- JavaScript para mostrar/ocultar secciones --}}
<script>
    // Mostrar/ocultar secciones según el tipo
    function toggleCategories(type) {
        const categorySection = document.getElementById('category-section');
        const labelIngreso = document.getElementById('label-ingreso');
        const labelEgreso = document.getElementById('label-egreso');
        
        if (type === 'egreso') {
            // EGRESO: Mostrar categorías
            categorySection.style.display = 'block';
            labelEgreso.classList.add('border-red-500', 'bg-red-50');
            labelIngreso.classList.remove('border-green-500', 'bg-green-50');
        } else {
            // INGRESO: Ocultar categorías
            categorySection.style.display = 'none';
            labelIngreso.classList.add('border-green-500', 'bg-green-50');
            labelEgreso.classList.remove('border-red-500', 'bg-red-50');
        }
    }

    // Mostrar campo personalizado si eligió "Otro"
    function toggleCustomCategory() {
        const category = document.getElementById('category').value;
        const customSection = document.getElementById('custom-category-section');
        if (category === 'Otro') {
            customSection.style.display = 'block';
        } else {
            customSection.style.display = 'none';
        }
    }

    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const typeRadios = document.querySelectorAll('input[name="type"]');
        typeRadios.forEach(radio => {
            if (radio.checked) {
                toggleCategories(radio.value);
            }
        });
        toggleCustomCategory();
    });
</script>
</x-app-layout>