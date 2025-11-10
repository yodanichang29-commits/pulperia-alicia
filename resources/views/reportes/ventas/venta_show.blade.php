<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">
      🧾 Detalle de venta #{{ $venta->id }} — <span class="text-blue-700">Pulpería Alicia</span>
    </h2>
   
  </x-slot>

 

  <div class="flex gap-2 mb-4">
    <a href="{{ url()->previous() }}" class="px-3 py-2 bg-gray-200 rounded-lg">← Volver</a>
    <button onclick="window.print()" class="px-3 py-2 bg-blue-600 text-white rounded-lg">Imprimir</button>
  </div>

  {{-- Encabezado --}}
  <div class="bg-white rounded-xl shadow p-5 space-y-2 mb-6">
    <p><strong>Fecha:</strong> {{ $venta->fecha }} &nbsp; <strong>Hora:</strong> {{ $venta->hora }}</p>
    <p><strong>Cajero:</strong> {{ $venta->cajero }}</p>
    <p><strong>Cliente:</strong> {{ $venta->cliente }}</p>
    <p><strong>Método:</strong>
      @switch($venta->payment)
        @case('cash') Efectivo @break
        @case('card') Tarjeta @break
        @case('transfer') Transferencia @break
        @case('credit') Crédito @break
        @default {{ $venta->payment }}
      @endswitch
    </p>
  </div>

  {{-- Totales --}}
  <div class="bg-white rounded-xl shadow p-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
    <div><span class="text-gray-500">Subtotal</span><div class="font-semibold">L {{ number_format($venta->subtotal ?? 0, 2) }}</div></div>
    <div><span class="text-gray-500">Recargo</span><div class="font-semibold">L {{ number_format($venta->surcharge ?? 0, 2) }}</div></div>
    <div><span class="text-gray-500">Total</span><div class="font-semibold">L {{ number_format($venta->total ?? 0, 2) }}</div></div>
    <div><span class="text-gray-500">Recibido</span><div class="font-semibold">L {{ number_format($venta->cash_received ?? 0, 2) }}</div></div>
    <div><span class="text-gray-500">Cambio</span><div class="font-semibold">L {{ number_format($venta->cash_change ?? 0, 2) }}</div></div>
  </div>

  {{-- Items --}}
  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-4 py-3 border-b"><h3 class="font-semibold">Productos</h3></div>
    <div class="p-4 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="p-2 text-left">Producto</th>
            <th class="p-2 text-right">Cantidad</th>
            <th class="p-2 text-right">Precio</th>
            <th class="p-2 text-right">Importe</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $it)
            <tr class="border-b last:border-0">
              <td class="p-2">{{ $it->producto }}</td>
              <td class="p-2 text-right">{{ number_format($it->qty, 2) }}</td>
              <td class="p-2 text-right">L {{ number_format($it->price, 2) }}</td>
              <td class="p-2 text-right">L {{ number_format($it->total, 2) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="p-3 text-gray-500">Sin productos.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
