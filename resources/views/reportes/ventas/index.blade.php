{{-- resources/views/reportes/ventas/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-800">
            📊 Reporte general de ventas — <span class="text-blue-700">Pulpería Alicia</span>
        </h2>

     
 @include('reportes.ventas.partials.nav')
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Filtros --}}
<form method="GET" action="{{ route('reportes.ventas.index') }}" class="bg-white rounded-xl p-4 shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Rango de fechas --}}
                <div>
                    <x-input-label for="start" value="Desde" />
                    <x-text-input id="start" type="date" name="start"
                                  value="{{ $filtros['start'] ?? '' }}" class="mt-1 block w-full"/>
                </div>
                <div>
                    <x-input-label for="end" value="Hasta" />
                    <x-text-input id="end" type="date" name="end"
                                  value="{{ $filtros['end'] ?? '' }}" class="mt-1 block w-full"/>
                </div>

                {{-- Usuario --}}
                <div>
                    <x-input-label for="user_id" value="Usuario (cajero)" />
                    <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">Todos</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}" @selected(($filtros['user_id'] ?? '') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Método --}}
                <div>
                    <x-input-label for="payment" value="Método de pago" />
                    <select id="payment" name="payment" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">Todos</option>
                        @foreach($metodos as $m)
                            <option value="{{ $m['key'] }}" @selected(($filtros['payment'] ?? '') === $m['key'])>{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <x-primary-button>Filtrar</x-primary-button>

                {{-- Exportar CSV con los mismos filtros --}}
                <a href="{{ route('reportes.ventas.export', request()->query()) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700">
                   Exportar CSV
                </a>
                 <a href="{{ route('reportes.ventas.excel', request()->query()) }}"
   class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-white hover:bg-emerald-700">
   Exportar Excel
</a>
            </div>
        </form>

        {{-- Resumen por día --}}
        <div class="bg-white rounded-xl shadow overflow-hidden mb-6">
            <div class="px-4 py-3 border-b">
                <h3 class="font-semibold">Resumen por día</h3>
                <p class="text-sm text-gray-500">
                    Rango: {{ $filtros['start'] }} a {{ $filtros['end'] }}
                </p>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left p-2">Fecha</th>
                            <th class="text-right p-2"># Ventas</th>
                            <th class="text-right p-2">Total</th>
                            <th class="text-right p-2">Efectivo</th>
                            <th class="text-right p-2">Tarjeta</th>
                            <th class="text-right p-2">Transferencia</th>
                            <th class="text-right p-2">Crédito</th>
                        </tr>
                        <tfoot>
    <tr class="bg-gray-50 font-semibold">
        <td class="p-2 text-right">Totales:</td>
        <td class="p-2 text-right">{{ $totalesDia['ventas'] }}</td>
        <td class="p-2 text-right">L {{ number_format($totalesDia['total'] ?? 0, 2) }}</td>
        <td class="p-2 text-right">L {{ number_format($totalesDia['efectivo'] ?? 0, 2) }}</td>
        <td class="p-2 text-right">L {{ number_format($totalesDia['tarjeta'] ?? 0, 2) }}</td>
        <td class="p-2 text-right">L {{ number_format($totalesDia['transferencia'] ?? 0, 2) }}</td>
        <td class="p-2 text-right">L {{ number_format($totalesDia['credito'] ?? 0, 2) }}</td>
    </tr>
</tfoot>

                    </thead>
                    <tbody>
                        @forelse($porDia as $r)
                            <tr class="border-b last:border-0">
                                <td class="p-2">{{ \Illuminate\Support\Carbon::parse($r->fecha)->translatedFormat('d/M/Y') }}</td>
                                <td class="p-2 text-right">{{ $r->ventas }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->total ?? 0, 2) }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->efectivo ?? 0, 2) }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->tarjeta ?? 0, 2) }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->transferencia ?? 0, 2) }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->credito ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td class="p-3 text-gray-500" colspan="7">Sin datos en el rango.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Resumen por usuario --}}
        <div class="bg-white rounded-xl shadow overflow-hidden mb-6">
            <div class="px-4 py-3 border-b">
                <h3 class="font-semibold">Resumen por usuario</h3>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left p-2">Usuario</th>
                            <th class="text-right p-2"># Ventas</th>
                            <th class="text-right p-2">Total</th>
                            <th class="text-right p-2">Efectivo</th>
                            <th class="text-right p-2">Tarjeta</th>
                            <th class="text-right p-2">Transferencia</th>
                            <th class="text-right p-2">Crédito</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($porUsuario as $r)
                            <tr class="border-b last:border-0">
                                <td class="p-2">{{ $r->usuario }}</td>
                                <td class="p-2 text-right">{{ $r->ventas }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->total ?? 0, 2) }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->efectivo ?? 0, 2) }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->tarjeta ?? 0, 2) }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->transferencia ?? 0, 2) }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->credito ?? 0, 2) }}</td>
                            </tr>

                        @empty
                            <tr><td class="p-3 text-gray-500" colspan="7">Sin datos en el rango.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Resumen por método --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-4 py-3 border-b">
                <h3 class="font-semibold">Resumen por método</h3>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left p-2">Método</th>
                            <th class="text-right p-2"># Ventas</th>
                            <th class="text-right p-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($porMetodo as $r)
                            <tr class="border-b last:border-0">
                                <td class="p-2 capitalize">
                                    @switch($r->metodo)
                                        @case('cash') Efectivo @break
                                        @case('card') Tarjeta @break
                                        @case('transfer') Transferencia @break
                                        @case('credit') Crédito @break
                                        @default {{ $r->metodo }}
                                    @endswitch
                                </td>
                                <td class="p-2 text-right">{{ $r->ventas }}</td>
                                <td class="p-2 text-right">L {{ number_format($r->total ?? 0, 2) }}</td>
                            </tr>






                        @empty
                            <tr><td class="p-3 text-gray-500" colspan="3">Sin datos en el rango.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
  <tr class="bg-gray-50 font-semibold">
    {{-- Columna "Método" --}}
    <td class="p-2 text-right">Totales:</td>

    {{-- # Ventas --}}
    <td class="p-2 text-right">
      {{ $totalesMetodo['ventas'] ?? 0 }}
    </td>

    {{-- Total L --}}
    <td class="p-2 text-right">
      L {{ number_format($totalesMetodo['total'] ?? 0, 2) }}
    </td>
  </tr>
</tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
