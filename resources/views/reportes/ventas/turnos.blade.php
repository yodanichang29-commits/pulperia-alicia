<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">Reporte general de ventas — Pulpería Alicia</h2>
    @include('reportes.ventas.partials.nav')
  </x-slot>

  <div class="max-w-7xl mx-auto p-4">

    {{-- Filtros de fecha --}}
    <form method="GET" action="{{ route('reportes.ventas.turnos') }}" class="bg-white p-4 rounded-xl shadow flex flex-col sm:flex-row gap-3 mb-4">
      <div>
        <label class="block text-sm text-gray-700">Desde</label>
        <input type="date" name="desde" value="{{ $desde }}"
               class="mt-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm text-gray-700">Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta }}"
               class="mt-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div class="flex items-end">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Filtrar
        </button>
      </div>
    </form>

    {{-- Tabla de turnos --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Apertura</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cierre</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto inicial</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Efectivo esperado</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Efectivo contado</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diferencia</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notas</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse($turnos as $turno)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $turno->usuario }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  {{ \Carbon\Carbon::parse($turno->opened_at)->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  {{ \Carbon\Carbon::parse($turno->closed_at)->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($turno->opening_float, 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($turno->expected_cash, 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($turno->closing_cash_count, 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right font-semibold 
                    {{ $turno->difference > 0 ? 'text-green-600' : ($turno->difference < 0 ? 'text-red-600' : 'text-gray-900') }}">
                  L {{ number_format($turno->difference, 2) }}
                  @if($turno->difference > 0)
                    <span class="text-xs">(Sobrante)</span>
                  @elseif($turno->difference < 0)
                    <span class="text-xs">(Faltante)</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  {{ $turno->notes ?: '-' }}
                </td>
                <td class="px-4 py-3 text-center">
  <a href="{{ route('reportes.ventas.turnos.detalle', $turno->id) }}" 
     class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
    Ver detalles
  </a>
</td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                  No hay turnos cerrados en este rango de fechas
                </td>
              </tr>
            @endforelse
          </tbody>

          {{-- Fila de totales --}}
          @if($turnos->isNotEmpty())
            <tfoot class="bg-gray-100 font-semibold">
              <tr>
                <td class="px-4 py-3 text-sm text-gray-900" colspan="3">
                  TOTALES ({{ $totales['turnos'] }} turnos)
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($totales['opening_float'], 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($totales['expected_cash'], 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">
                  L {{ number_format($totales['closing_cash_count'], 2) }}
                </td>
                <td class="px-4 py-3 text-sm text-right font-bold
                    {{ $totales['difference'] > 0 ? 'text-green-600' : ($totales['difference'] < 0 ? 'text-red-600' : 'text-gray-900') }}">
                  L {{ number_format($totales['difference'], 2) }}
                </td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3"></td>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </div>

  </div>
</x-app-layout>