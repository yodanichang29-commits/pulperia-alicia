@csrf
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <div>
    <label for="name" class="block text-sm font-medium text-gray-700">Nombre del proveedor</label>
    <input id="name" name="name" type="text" required
           value="{{ old('name', $provider->name) }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
    @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
  </div>

  <div>
    <label for="contact_name" class="block text-sm font-medium text-gray-700">Contacto</label>
    <input id="contact_name" name="contact_name" type="text"
           value="{{ old('contact_name', $provider->contact_name) }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
  </div>

  <div>
    <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
    <input id="phone" name="phone" type="text"
           value="{{ old('phone', $provider->phone) }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
  </div>

  <div>
    <label for="email" class="block text-sm font-medium text-gray-700">Correo</label>
    <input id="email" name="email" type="email"
           value="{{ old('email', $provider->email) }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
    @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="sm:col-span-2">
    <label for="notes" class="block text-sm font-medium text-gray-700">Notas</label>
    <textarea id="notes" name="notes" rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $provider->notes) }}</textarea>
  </div>

  <div class="sm:col-span-2 flex items-center gap-2">
    <input id="active" name="active" type="checkbox" value="1"
           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
           {{ old('active', $provider->active) ? 'checked' : '' }}>
    <label for="active" class="text-sm text-gray-700">Activo</label>
  </div>
</div>

<div class="mt-6 flex items-center gap-3">
  <button type="submit"
          class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
    {{ $btn ?? 'Guardar' }}
  </button>
  <a href="{{ route('proveedores.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
