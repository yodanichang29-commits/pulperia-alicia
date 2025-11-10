<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">📊 Finanzas — Resumen por rango</h2>
  </x-slot>

  <div class="p-6 space-y-6">

    {{-- Filtros --}}
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 items-end gap-4 bg-white p-4 rounded-xl shadow">
    <div class="flex items-center gap-2">
    <input type="date" name="start" value="{{ request('start') }}" class="border rounded px-3 py-2">
    <input type="date" name="end" value="{{ request('end') }}" class="border rounded px-3 py-2">

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        Filtrar
    </button>

    <button type="button"
        onclick="window.location.href='{{ route('finanzas.index') }}'"
        class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition">
        Limpiar
    </button>
</div>

    </form>

    {{-- Sección: Entradas reales a caja (VERDE) --}}
    <section class="bg-white p-4 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-3">Entró a caja (rango)</h3>
      <p class="text-sm text-gray-500 mb-4">Contado + abonos. <span class="font-medium">No</span> incluye ventas a crédito.</p>

      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
          $cardsGreen = [
            ['label'=>'Efectivo','value'=>$ventasEfectivo],
            ['label'=>'Tarjeta','value'=>$ventasTarjeta],
            ['label'=>'Transferencia','value'=>$ventasTransf],
            ['label'=>'Abonos a crédito','value'=>$abonosCredito],
          ];
        @endphp
        @foreach($cardsGreen as $c)
          <div class="rounded-xl border p-4">
            <p class="text-gray-600 text-sm">{{ $c['label'] }}</p>
            <p class="text-2xl font-bold text-emerald-600">L {{ number_format($c['value'],2) }}</p>
          </div>
        @endforeach
      </div>

      <div class="mt-4 rounded-xl border p-4 bg-emerald-50">
        <p class="text-gray-600 text-sm">Entradas a caja (total)</p>
        <p class="text-2xl font-bold text-emerald-700">L {{ number_format($entradasCaja,2) }}</p>
      </div>
    </section>

    {{-- Sección: No entra / Salidas (ROJO) --}}
    <section class="bg-white p-4 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-3">No entra / Salidas (rango)</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="rounded-xl border p-4">
          <p class="text-gray-600 text-sm">Ventas a crédito (no entra)</p>
          <p class="text-2xl font-bold text-red-600">L {{ number_format($ventasCredito,2) }}</p>
        </div>
        <div class="rounded-xl border p-4">
          <p class="text-gray-600 text-sm">Ingresos de mercadería (compras)</p>
          <p class="text-2xl font-bold text-red-600">L {{ number_format($compras,2) }}</p>
        </div>
        <div class="rounded-xl border p-4">
          <p class="text-gray-600 text-sm">Egresos por merma/pérdida</p>
          <p class="text-2xl font-bold text-red-600">L {{ number_format($mermas,2) }}</p>
        </div>
      </div>

      <div class="mt-4 grid sm:grid-cols-2 gap-4">
        <div class="rounded-xl border p-4 bg-gray-50">
          <p class="text-gray-600 text-sm">Valor actual del inventario (a costo)</p>
          <p class="text-2xl font-bold text-gray-800">L {{ number_format($valorInventario,2) }}</p>
        </div>

        <div class="rounded-xl border p-4 {{ $balance >= 0 ? 'bg-emerald-50' : 'bg-red-50' }}">
          <p class="text-gray-600 text-sm">Balance general (rango)</p>
          <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
            L {{ number_format($balance,2) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Entradas reales (efectivo + tarjeta + transferencia + abonos) − (compras + mermas)
          </p>
        </div>
      </div>
    </section>

    {{-- Ventas por día (tabla simple) --}}
    <section class="bg-white p-4 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-3">Ventas por día</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600 border-b">
              <th class="py-2 pr-4">Fecha</th>
              <th class="py-2">Total</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ventasPorDia as $row)
              <tr class="border-b">
                <td class="py-2 pr-4">{{ $row->fecha }}</td>
                <td class="py-2 font-semibold">L {{ number_format($row->total,2) }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="py-3 text-gray-500">Sin ventas en el rango.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    {{-- Ventas por mes (tabla simple últimos 12) --}}
    <section class="bg-white p-4 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-3">Ventas por mes (últimos 12)</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600 border-b">
              <th class="py-2 pr-4">Mes</th>
              <th class="py-2">Total</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ventasPorMes as $row)
              <tr class="border-b">
                <td class="py-2 pr-4">{{ $row->ym }}</td>
                <td class="py-2 font-semibold">L {{ number_format($row->total,2) }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="py-3 text-gray-500">Sin datos.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

  </div>
</x-app-layout>
