{{-- resources/views/reportes/cxc_show.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800">
        👤 {{ $client->name }} — Detalle Credito
      </h2>
      <a href="{{ route('reportes.cxc', request()->only(['from','to'])) }}"
         class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300">← Volver</a>
    </div>
  </x-slot>

  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 space-y-6">
    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white rounded-2xl shadow ring-1 ring-black/5 p-5">
        <div class="text-gray-500">Total a crédito</div>
        <div class="text-2xl font-bold">L {{ number_format($total_credito, 2) }}</div>
      </div>
      <div class="bg-white rounded-2xl shadow ring-1 ring-black/5 p-5">
        <div class="text-gray-500">Total abonado</div>
        <div class="text-2xl font-bold">L {{ number_format($total_abonos, 2) }}</div>
      </div>
      <div class="bg-white rounded-2xl shadow ring-1 ring-black/5 p-5">
        <div class="text-gray-500">Saldo pendiente</div>
        <div class="text-2xl font-bold text-blue-700">L {{ number_format($saldo, 2) }}</div>
      </div>
    </div>

    {{-- Ventas a crédito --}}
    <div class="bg-white rounded-2xl shadow ring-1 ring-black/5 overflow-hidden">
      <div class="px-5 py-3 border-b font-semibold">Ventas a crédito</div>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 text-gray-600">
          <tr class="text-sm">
            <th class="px-4 py-3 text-left">#</th>
            <th class="px-4 py-3 text-right">Total</th>
            <th class="px-4 py-3 text-left">Fecha</th>
          </tr>
          </thead>
          <tbody class="divide-y">
          @forelse($ventasCredito as $v)
            <tr>
              <td class="px-4 py-3">V{{ $v->id }}</td>
              <td class="px-4 py-3 text-right">L {{ number_format($v->total, 2) }}</td>
              <td class="px-4 py-3">{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y h:i a') }}</td>
            </tr>
          @empty
            <tr><td class="px-4 py-4 text-center text-gray-500" colspan="3">—</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Abonos --}}
    <div class="bg-white rounded-2xl shadow ring-1 ring-black/5 overflow-hidden">
      <div class="px-5 py-3 border-b font-semibold">Abonos</div>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 text-gray-600">
          <tr class="text-sm">
            <th class="px-4 py-3 text-left">#</th>
            <th class="px-4 py-3 text-right">Monto</th>
            <th class="px-4 py-3 text-left">Método</th>
            <th class="px-4 py-3 text-left">Fecha</th>
          </tr>
          </thead>
          <tbody class="divide-y">
          @forelse($abonos as $a)
            <tr>
              <td class="px-4 py-3">A{{ $a->id }}</td>
              <td class="px-4 py-3 text-right">L {{ number_format($a->amount, 2) }}</td>
              <td class="px-4 py-3">{{ strtoupper($a->method) }}</td>
              <td class="px-4 py-3">{{ \Carbon\Carbon::parse($a->created_at)->format('d/m/Y h:i a') }}</td>
            </tr>
          @empty
            <tr><td class="px-4 py-4 text-center text-gray-500" colspan="4">—</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>
