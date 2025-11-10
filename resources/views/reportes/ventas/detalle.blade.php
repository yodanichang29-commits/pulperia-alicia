<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">
      🧾 Reporte de ventas (detalle) — <span class="text-blue-700">Pulpería Alicia</span>
    </h2>
        @include('reportes.ventas.partials.nav')
  </x-slot>

  {{-- Filtros --}}
  <div class="bg-white rounded-xl shadow p-4 mb-6">
    <form method="GET" action="{{ route('reportes.ventas.detalle') }}" class="grid gap-4 md:grid-cols-5">
      <div>
        <x-input-label value="Desde"/>
        <x-text-input type="date" name="start" value="{{ $filtros['start'] }}" class="mt-1 w-full"/>
      </div>
      <div>
        <x-input-label value="Hasta"/>
        <x-text-input type="date" name="end" value="{{ $filtros['end'] }}" class="mt-1 w-full"/>
      </div>
      <div>
        <x-input-label value="Cajero"/>
        <select name="user_id" class="mt-1 w-full border-gray-300 rounded-lg">
          <option value="">Todos</option>
          @foreach($usuarios as $u)
            <option value="{{ $u->id }}" @selected($filtros['userId']==$u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <x-input-label value="Método de pago"/>
        <select name="payment" class="mt-1 w-full border-gray-300 rounded-lg">
          <option value="">Todos</option>
          <option value="cash"     @selected($filtros['payment']=='cash')>Efectivo</option>
          <option value="card"     @selected($filtros['payment']=='card')>Tarjeta</option>
          <option value="transfer" @selected($filtros['payment']=='transfer')>Transferencia</option>
          <option value="credit"   @selected($filtros['payment']=='credit')>Crédito</option>
        </select>
      </div>
      <div>
        <x-input-label value="ID Venta (opcional)"/>
        <x-text-input type="number" name="sale_id" value="{{ $filtros['qSaleId'] ?? '' }}" class="mt-1 w-full"/>
      </div>

      <div class="md:col-span-5">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filtrar</button>
      </div>
    </form>
  </div>

  {{-- Tabla de ventas (cabeceras) --}}
  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-4 py-3 border-b">
      <h3 class="font-semibold">Ventas del {{ $filtros['start'] }} al {{ $filtros['end'] }}</h3>
    </div>

    <div class="p-4 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="p-2 text-left">Venta</th>
            <th class="p-2 text-left">Fecha</th>
            <th class="p-2 text-left">Hora</th>
            <th class="p-2 text-left">Cajero</th>
            <th class="p-2 text-left">Cliente</th>
            <th class="p-2 text-left">Método</th>
            <th class="p-2 text-right">Subtotal</th>
            <th class="p-2 text-right">Recargo</th>
            <th class="p-2 text-right">Total</th>
            <th class="p-2 text-right">Recibido</th>
            <th class="p-2 text-right">Cambio</th>
            <th class="p-2"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($ventas as $v)
            <tr class="border-b last:border-0 align-top">
              <td class="p-2">#{{ $v->id }}</td>
              <td class="p-2">{{ $v->fecha }}</td>
            <td class="p-2">
  {{ \Carbon\Carbon::parse($v->hora)->format('h:i:s A') }}
</td>

              <td class="p-2">{{ $v->cajero }}</td>
              <td class="p-2">{{ $v->cliente }}</td>
              <td class="p-2 capitalize">
                @switch($v->payment)
                  @case('cash') Efectivo @break
                  @case('card') Tarjeta @break
                  @case('transfer') Transferencia @break
                  @case('credit') Crédito @break
                  @default {{ $v->payment }}
                @endswitch
              </td>
              <td class="p-2 text-right">L {{ number_format($v->subtotal ?? 0, 2) }}</td>
              <td class="p-2 text-right">L {{ number_format($v->surcharge ?? 0, 2) }}</td>
              <td class="p-2 text-right font-medium">L {{ number_format($v->total ?? 0, 2) }}</td>
              <td class="p-2 text-right">L {{ number_format($v->cash_received ?? 0, 2) }}</td>
              <td class="p-2 text-right">L {{ number_format($v->cash_change ?? 0, 2) }}</td>
             <td class="p-2">
  <a href="{{ route('reportes.ventas.show', $v->id) }}"
     class="inline-flex items-center gap-1 text-blue-600 hover:underline">
     Ver detalle
     <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
     </svg>
  </a>
</td>

            </tr>
          @empty
            <tr><td class="p-4 text-gray-500" colspan="12">Sin ventas en el rango.</td></tr>
          @endforelse
        </tbody>

        {{-- Pie de totales --}}
        <tfoot>
          <tr class="bg-gray-50 font-semibold">
            <td class="p-2 text-left" colspan="3">Ventas: {{ $totales['ventas'] }}</td>
            <td class="p-2" colspan="2"></td>
            <td class="p-2 text-right">Totales:</td>
            <td class="p-2 text-right">L {{ number_format($totales['subtotal'], 2) }}</td>
            <td class="p-2 text-right">L {{ number_format($totales['surcharge'], 2) }}</td>
            <td class="p-2 text-right">L {{ number_format($totales['total'], 2) }}</td>
            <td class="p-2 text-right">L {{ number_format($totales['cash'], 2) }}</td>
            <td class="p-2 text-right">L {{ number_format($totales['change'], 2) }}</td>
            <td class="p-2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</x-app-layout>
