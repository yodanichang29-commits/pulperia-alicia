@php $fmt = fn($n)=> 'L '.number_format($n,2); @endphp

<div class="overflow-x-auto bg-white rounded-xl shadow">
  <table class="min-w-full border-collapse">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="px-4 py-3">Proveedor</th>
        <th class="px-4 py-3">Unidades</th>
        <th class="px-4 py-3">Total vendido</th>
        <th class="px-4 py-3">% del total</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse($rows as $r)
        @php $pct = $total_general>0 ? round(($r->total_vendido/$total_general)*100,2) : 0; @endphp
        <tr class="text-sm">
          <td class="px-4 py-3 font-medium text-gray-900">{{ $r->proveedor }}</td>
          <td class="px-4 py-3">{{ (int)$r->unidades }}</td>
          <td class="px-4 py-3">{{ $fmt($r->total_vendido) }}</td>
          <td class="px-4 py-3">{{ $pct }}%</td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin datos en el rango seleccionado</td></tr>
      @endforelse
    </tbody>
    <tfoot class="bg-gray-50 text-sm">
      <tr>
        <th class="px-4 py-3 text-right">Total general:</th>
        <th class="px-4 py-3"></th>
        <th class="px-4 py-3">{{ $fmt($total_general) }}</th>
        <th class="px-4 py-3">100%</th>
      </tr>
    </tfoot>
  </table>
</div>
