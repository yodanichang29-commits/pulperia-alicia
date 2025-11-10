{{-- resources/views/reportes/cxc.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800">
        📊 Reporte — Cuentas por Cobrar
      </h2>
      <a href="{{ route('reportes.cxc.export', request()->query()) }}"
         class="px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700">
        Exportar CSV
      </a>
    </div>
  </x-slot>

  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6">
    {{-- Filtros --}}
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
      <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar cliente o teléfono"
             class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 px-4 py-3 text-lg">
      <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
             class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 px-4 py-3 text-lg">
      <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
             class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 px-4 py-3 text-lg">
      <button class="rounded-xl bg-blue-600 text-white px-4 py-3 text-lg hover:bg-blue-700">Filtrar</button>
    </form>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl shadow ring-1 ring-black/5 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-left align-middle">
          <thead class="bg-gray-50 text-gray-600">
            <tr class="text-sm">
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Teléfono</th>
              <th class="px-4 py-3 text-right">Total a crédito</th>
              <th class="px-4 py-3 text-right">Total abonado</th>
              <th class="px-4 py-3 text-right">Saldo pendiente</th>
              <th class="px-4 py-3">Último abono</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @forelse ($clients as $c)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                <td class="px-4 py-3">{{ $c->phone }}</td>
                <td class="px-4 py-3 text-right">L {{ number_format($c->total_credit ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-right">L {{ number_format($c->total_paid ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-blue-700">L {{ number_format($c->saldo, 2) }}</td>
                <td class="px-4 py-3">
{{ $c->last_payment_at ? \Carbon\Carbon::parse($c->last_payment_at)->format('d/m/Y h:i a') : '—' }}
                </td>
                <td class="px-4 py-3">
                  <a
                    href="{{ route('reportes.cxc.show', ['client'=>$c->id] + request()->only(['from','to'])) }}"
                    class="px-3 py-2 rounded-xl bg-gray-800 text-white hover:bg-black text-sm">
                    Ver detalle
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin resultados</td>
              </tr>
            @endforelse
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td class="px-4 py-4 font-semibold" colspan="4">Total de saldos visibles</td>
              <td class="px-4 py-4 text-right font-bold text-blue-800">
                L {{ number_format($total_saldos, 2) }}
              </td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>
