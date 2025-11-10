@php $fmt = fn($n)=> 'L '.number_format($n,2); @endphp

<div class="overflow-x-auto bg-white rounded-xl shadow">
  <table class="min-w-full border-collapse">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="px-4 py-3">Producto</th>
        <th class="px-4 py-3">Proveedor</th>
        <th class="px-4 py-3">Unidades</th>
        <th class="px-4 py-3">Total vendido</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse($detalle as $r)
        <tr class="text-sm">
          <td class="px-4 py-3 font-medium text-gray-900">{{ $r->producto }}</td>
          <td class="px-4 py-3">{{ $r->proveedor }}</td>
          <td class="px-4 py-3">{{ (int)$r->unidades }}</td>
          <td class="px-4 py-3">{{ $fmt($r->total_vendido) }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin productos en el rango/búsqueda</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
