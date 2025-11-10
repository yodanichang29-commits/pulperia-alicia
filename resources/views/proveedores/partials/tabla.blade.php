<div class="overflow-x-auto bg-white rounded-xl shadow">
  <table class="min-w-full border-collapse">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="px-4 py-3">Proveedor</th>
        <th class="px-4 py-3">Contacto</th>
        <th class="px-4 py-3">Teléfono</th>
        <th class="px-4 py-3">Correo</th>
        <th class="px-4 py-3">Estado</th>
        <th class="px-4 py-3 text-right">Acciones</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($providers as $p)
        <tr class="text-sm">
          <td class="px-4 py-3 font-medium text-gray-900">{{ $p->name }}</td>
          <td class="px-4 py-3">{{ $p->contact_name ?: '—' }}</td>
          <td class="px-4 py-3">{{ $p->phone ?: '—' }}</td>
          <td class="px-4 py-3">{{ $p->email ?: '—' }}</td>
          <td class="px-4 py-3">
            @if ($p->active)
              <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Activo</span>
            @else
              <span class="px-2 py-1 rounded-full text-xs bg-gray-200 text-gray-700">Inactivo</span>
            @endif
          </td>
          <td class="px-4 py-3">
            <div class="flex gap-2 justify-end">
              <a href="{{ route('proveedores.edit', $p) }}" class="text-indigo-700 hover:underline">Editar</a>
              <form action="{{ route('proveedores.destroy', $p) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar proveedor?');">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Eliminar</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin proveedores</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $providers->links() }}</div>
