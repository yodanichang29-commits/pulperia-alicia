<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ➕ Nueva Categoría
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                
                <form action="{{ route('categorias.store') }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre de la categoría <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $category->name) }}"
                                   class="w-full rounded-lg border-gray-300 @error('name') border-red-500 @enderror"
                                   placeholder="Ej: Frescos Coca-Cola"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción (opcional)
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3"
                                      class="w-full rounded-lg border-gray-300"
                                      placeholder="Para apartar dinero del distribuidor de Coca-Cola">{{ old('description', $category->description) }}</textarea>
                        </div>

                        <div>
                            <label for="order" class="block text-sm font-medium text-gray-700 mb-1">
                                Orden de aparición <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="order" 
                                   id="order" 
                                   value="{{ old('order', $category->order) }}"
                                   class="w-full rounded-lg border-gray-300"
                                   min="0"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">
                                Las categorías con menor número aparecen primero en el POS
                            </p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="active" 
                                   id="active" 
                                   value="1"
                                   {{ old('active', $category->active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600">
                            <label for="active" class="ml-2 text-sm text-gray-700">
                                Categoría activa
                            </label>
                        </div>

                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('categorias.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Guardar Categoría
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>