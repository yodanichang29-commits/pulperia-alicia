<x-app-layout>
  <x-slot name="header">
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
    <h2 class="font-semibold text-xl text-gray-800">Detalle del Turno #{{ $turno->id }}</h2>
    <a href="{{ route('reportes.ventas.turnos') }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Volver a turnos
    </a>
  </div>
</x-slot>

  <div class="max-w-7xl mx-auto p-4 space-y-6">

    {{-- Información general del turno --}}
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Información General</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <p class="text-sm text-gray-500">Usuario</p>
          <p class="text-base font-semibold text-gray-900">{{ $turno->usuario }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Apertura</p>
          <p class="text-base font-semibold text-gray-900">
            {{ \Carbon\Carbon::parse($turno->opened_at)->format('d/m/Y H:i') }}
          </p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Cierre</p>
          <p class="text-base font-semibold text-gray-900">
            {{ \Carbon\Carbon::parse($turno->closed_at)->format('d/m/Y H:i') }}
          </p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Duración</p>
          <p class="text-base font-semibold text-gray-900">
            @php
              $inicio = \Carbon\Carbon::parse($turno->opened_at);
              $fin = \Carbon\Carbon::parse($turno->closed_at);
              $diff = $inicio->diff($fin);
            @endphp
            {{ $diff->h }}h {{ $diff->i }}min
          </p>
        </div>
      </div>

      @if($turno->notes)
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p class="text-sm text-gray-600"><strong>Notas:</strong> {{ $turno->notes }}</p>
        </div>
      @endif
    </div>

    {{-- Resumen de efectivo --}}
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">💰 Resumen de Efectivo</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-blue-50 rounded-lg">
          <p class="text-sm text-gray-600">Monto inicial</p>
          <p class="text-2xl font-bold text-blue-700">L {{ number_format($turno->opening_float, 2) }}</p>
        </div>
        <div class="p-4 bg-green-50 rounded-lg">
          <p class="text-sm text-gray-600">Efectivo esperado</p>
          <p class="text-2xl font-bold text-green-700">L {{ number_format($turno->expected_cash, 2) }}</p>
        </div>
        <div class="p-4 bg-purple-50 rounded-lg">
          <p class="text-sm text-gray-600">Efectivo contado</p>
          <p class="text-2xl font-bold text-purple-700">L {{ number_format($turno->closing_cash_count, 2) }}</p>
        </div>
        <div class="p-4 {{ $turno->difference >= 0 ? 'bg-green-50' : 'bg-red-50' }} rounded-lg">
          <p class="text-sm text-gray-600">Diferencia</p>
          <p class="text-2xl font-bold {{ $turno->difference >= 0 ? 'text-green-700' : 'text-red-700' }}">
            L {{ number_format($turno->difference, 2) }}
          </p>
          <p class="text-xs {{ $turno->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $turno->difference > 0 ? 'Sobrante' : ($turno->difference < 0 ? 'Faltante' : 'Cuadrado') }}
          </p>
        </div>
      </div>
    </div>

    {{-- Resumen general de ventas --}}
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Resumen General</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-200">
          <p class="text-sm text-gray-600">Total de ventas</p>
          <p class="text-3xl font-bold text-indigo-700">{{ $totalVentas }}</p>
          <p class="text-xs text-gray-500">tickets emitidos</p>
        </div>
        <div class="p-4 bg-teal-50 rounded-lg border border-teal-200">
          <p class="text-sm text-gray-600">Unidades vendidas</p>
          <p class="text-3xl font-bold text-teal-700">{{ number_format($totalUnidades) }}</p>
          <p class="text-xs text-gray-500">productos</p>
        </div>
        <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-200">
          <p class="text-sm text-gray-600">Total vendido</p>
          <p class="text-3xl font-bold text-emerald-700">L {{ number_format($totalVendido, 2) }}</p>
          <p class="text-xs text-gray-500">todos los métodos</p>
        </div>
      </div>
    </div>

    {{-- Ventas por método de pago --}}
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">💳 Ventas por Método de Pago</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad de ventas</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($metodos as $key => $metodo)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $metodo['label'] }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $metodo['cantidad'] }}</td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                  L {{ number_format($metodo['total'], 2) }}
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot class="bg-gray-100 font-semibold">
            <tr>
              <td class="px-4 py-3 text-sm text-gray-900">TOTAL</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">
                {{ array_sum(array_column($metodos, 'cantidad')) }}
              </td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">
                L {{ number_format(array_sum(array_column($metodos, 'total')), 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- Devoluciones --}}
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">🔄 Devoluciones (solo efectivo)</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 bg-orange-50 rounded-lg border border-orange-200">
          <p class="text-sm text-gray-600">Cantidad de devoluciones</p>
          <p class="text-3xl font-bold text-orange-700">
            {{ $devoluciones->cantidad_devoluciones ?? 0 }}
          </p>
        </div>
        <div class="p-4 bg-red-50 rounded-lg border border-red-200">
          <p class="text-sm text-gray-600">Total devuelto</p>
          <p class="text-3xl font-bold text-red-700">
            L {{ number_format($devoluciones->total_devuelto ?? 0, 2) }}
          </p>
        </div>
      </div>
    </div>

    {{-- Abonos de clientes --}}
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">💵 Abonos de Clientes (CxC)</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad de abonos</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">Efectivo</td>
              <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $abonos['efectivo']['cantidad'] }}</td>
              <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                L {{ number_format($abonos['efectivo']['total'], 2) }}
              </td>
            </tr>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">Tarjeta</td>
              <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $abonos['tarjeta']['cantidad'] }}</td>
              <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                L {{ number_format($abonos['tarjeta']['total'], 2) }}
              </td>
            </tr>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">Transferencia</td>
              <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $abonos['transferencia']['cantidad'] }}</td>
              <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                L {{ number_format($abonos['transferencia']['total'], 2) }}
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-100 font-semibold">
            <tr>
              <td class="px-4 py-3 text-sm text-gray-900">TOTAL</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">
                {{ $abonos['efectivo']['cantidad'] + $abonos['tarjeta']['cantidad'] + $abonos['transferencia']['cantidad'] }}
              </td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">
                L {{ number_format($abonos['efectivo']['total'] + $abonos['tarjeta']['total'] + $abonos['transferencia']['total'], 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>





    {{-- Tabla detallada de ventas --}}
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">🧾 Detalle de Ventas del Turno</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Venta</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cajero</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Recargo</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Recibido</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cambio</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse($ventas as $venta)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                  #{{ $venta->id }}
          
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  {{ \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y') }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  {{ \Carbon\Carbon::parse($venta->created_at)->format('h:i:s A') }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $venta->cajero }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $venta->cliente ?: '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">
                  @php
                    $metodoLabels = [
                      'cash' => 'Efectivo',
                      'card' => 'Tarjeta',
                      'transfer' => 'Transferencia',
                      'credit' => 'Crédito'
                    ];
                  @endphp
                  {{ $metodoLabels[$venta->payment] ?? $venta->payment }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($venta->subtotal, 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($venta->surcharge ?? 0, 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                  L {{ number_format($venta->total, 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($venta->cash_received ?? 0, 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($venta->cash_change ?? 0, 2) }}
                </td>
                <td class="px-4 py-3 text-center">
               <a href="{{ route('reportes.ventas.show', $venta->id) }}" 
   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
  Ver detalle →
</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="12" class="px-4 py-8 text-center text-gray-500">
                  No hay ventas en este turno
                </td>
              </tr>
            @endforelse
          </tbody>

          {{-- Totales --}}
          @if($ventas->isNotEmpty())
            <tfoot class="bg-gray-100 font-semibold">
              <tr>
                <td colspan="6" class="px-4 py-3 text-sm text-gray-900">
                  TOTALES ({{ $ventas->count() }} ventas)
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($ventas->sum('subtotal'), 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($ventas->sum('surcharge'), 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($ventas->sum('total'), 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($ventas->sum('cash_received'), 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($ventas->sum('cash_change'), 2) }}
                </td>
                <td class="px-4 py-3"></td>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </div>





  </div>
</x-app-layout>