<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">💰 Balance General de la Pulpería</h2>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- FILTROS --}}
        <form method="GET" class="bg-white p-4 rounded-xl shadow">
            <div class="flex items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">📅 Desde</label>
                    <input type="date" name="start" value="{{ $start }}" 
                           class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">📅 Hasta</label>
                    <input type="date" name="end" value="{{ $end }}" 
                           class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                    Filtrar
                </button>
                <a href="{{ route('finanzas.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold transition">
                    Limpiar
                </a>
            </div>
            <p class="text-sm text-gray-600 mt-2">📊 Período: {{ $diasPeriodo }} días</p>
        </form>

        {{-- DEBUG INFO --}}
        <div class="bg-purple-50 border-l-4 border-purple-500 rounded-xl p-4">
            <p class="font-bold text-purple-800 mb-2">🔍 Información de Debug - Movimientos de Caja</p>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-purple-600">Total registros en rango:</span>
                    <span class="font-bold text-purple-900">{{ $totalCashMovements }}</span>
                </div>
                <div>
                    <span class="text-purple-600">Ingresos encontrados:</span>
                    <span class="font-bold text-purple-900">{{ $countIngresos }}</span>
                </div>
                <div>
                    <span class="text-purple-600">Egresos encontrados:</span>
                    <span class="font-bold text-purple-900">{{ $countEgresos }}</span>
                </div>
            </div>
            <div class="mt-2 text-xs text-purple-600">
                <p><strong>Rango:</strong> {{ Carbon\Carbon::parse($start)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>
                <p><strong>Otros Ingresos calculados:</strong> L {{ number_format($otrosIngresos, 2) }}</p>
                <p><strong>Gastos Operativos calculados:</strong> L {{ number_format($gastosOperativos, 2) }}</p>
            </div>
        </div>

        {{-- ALERTAS --}}
        @if(count($alertas) > 0)
        <div class="space-y-3">
            @foreach($alertas as $alerta)
                <div class="rounded-xl p-4 border-l-4
                    {{ $alerta['tipo'] === 'success' ? 'bg-emerald-50 border-emerald-500 text-emerald-800' : '' }}
                    {{ $alerta['tipo'] === 'warning' ? 'bg-yellow-50 border-yellow-500 text-yellow-800' : '' }}
                    {{ $alerta['tipo'] === 'danger' ? 'bg-red-50 border-red-500 text-red-800' : '' }}">
                    <p class="font-semibold">{{ $alerta['icono'] }} {{ $alerta['mensaje'] }}</p>
                </div>
            @endforeach
        </div>
        @endif

        {{-- TARJETAS DE RESUMEN --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Total Entradas --}}
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-emerald-100 text-sm font-medium">💰 Total Entradas</p>
                    <div class="bg-white/20 rounded-lg px-2 py-1">
                        <span class="text-xs font-bold">
                            {{ $cambioEntradas > 0 ? '↑' : '↓' }} {{ number_format(abs($cambioEntradas), 1) }}%
                        </span>
                    </div>
                </div>
                <p class="text-4xl font-black mb-1">L {{ number_format($totalEntradas, 2) }}</p>
                <p class="text-emerald-100 text-xs">vs período anterior: L {{ number_format($prevEntradas, 2) }}</p>
            </div>

            {{-- Total Salidas --}}
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-red-100 text-sm font-medium">💸 Total Salidas</p>
                    <div class="bg-white/20 rounded-lg px-2 py-1">
                        <span class="text-xs font-bold">
                            {{ $cambioSalidas > 0 ? '↑' : '↓' }} {{ number_format(abs($cambioSalidas), 1) }}%
                        </span>
                    </div>
                </div>
                <p class="text-4xl font-black mb-1">L {{ number_format($totalSalidas, 2) }}</p>
                <p class="text-red-100 text-xs">vs período anterior: L {{ number_format($prevSalidas, 2) }}</p>
            </div>

            {{-- Balance Neto --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-blue-100 text-sm font-medium">💵 Balance de Caja</p>
                    <div class="bg-white/20 rounded-lg px-2 py-1">
                        <span class="text-xs font-bold">
                            {{ $cambioBalance > 0 ? '↑' : '↓' }} {{ number_format(abs($cambioBalance), 1) }}%
                        </span>
                    </div>
                </div>
                <p class="text-4xl font-black mb-1">L {{ number_format($balance, 2) }}</p>
                <p class="text-blue-100 text-xs">vs período anterior: L {{ number_format($prevBalance, 2) }}</p>
            </div>

            {{-- Ganancia Bruta --}}
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-purple-100 text-sm font-medium">📈 Ganancia Bruta</p>
                    <div class="bg-white/20 rounded-lg px-2 py-1">
                        <span class="text-xs font-bold">
                            {{ $cambioGanancia > 0 ? '↑' : '↓' }} {{ number_format(abs($cambioGanancia), 1) }}%
                        </span>
                    </div>
                </div>
                <p class="text-4xl font-black mb-1">L {{ number_format($gananciaBruta, 2) }}</p>
                <p class="text-purple-100 text-xs">Margen: {{ number_format($margenGanancia, 1) }}%</p>
            </div>

        </div>

      {{-- SEGUNDA FILA DE TARJETAS --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- Valor Inventario --}}
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-indigo-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-gray-600 text-sm font-medium">📦 Valor del Inventario</p>
            <div class="bg-gray-100 rounded-lg px-2 py-1">
                <span class="text-xs font-bold text-gray-600">
                    Sin cambio
                </span>
            </div>
        </div>
        <p class="text-3xl font-black text-gray-800 mb-1">L {{ number_format($valorInventario, 2) }}</p>
        <p class="text-xs text-gray-500">Valor actual a costo</p>
    </div>

    {{-- Por Cobrar --}}
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-gray-600 text-sm font-medium">💳 Créditos Pendientes</p>
            <div class="bg-orange-100 rounded-lg px-2 py-1">
                <span class="text-xs font-bold {{ $cambioPorCobrar > 0 ? 'text-orange-700' : ($cambioPorCobrar < 0 ? 'text-emerald-700' : 'text-gray-600') }}">
                    @if($cambioPorCobrar > 0)
                        ↑ {{ number_format(abs($cambioPorCobrar), 1) }}%
                    @elseif($cambioPorCobrar < 0)
                        ↓ {{ number_format(abs($cambioPorCobrar), 1) }}%
                    @else
                        Sin cambio
                    @endif
                </span>
            </div>
        </div>
        <p class="text-3xl font-black text-gray-800 mb-1">L {{ number_format($porCobrar, 2) }}</p>
        <p class="text-xs text-gray-500">vs período anterior: L {{ number_format($prevPorCobrar, 2) }}</p>
    </div>

    {{-- Capital Total --}}
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-teal-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-gray-600 text-sm font-medium">💎 Capital Total Estimado</p>
            <div class="bg-teal-100 rounded-lg px-2 py-1">
                <span class="text-xs font-bold {{ $cambioCapital > 0 ? 'text-emerald-700' : ($cambioCapital < 0 ? 'text-red-700' : 'text-gray-600') }}">
                    @if($cambioCapital > 0)
                        ↑ {{ number_format(abs($cambioCapital), 1) }}%
                    @elseif($cambioCapital < 0)
                        ↓ {{ number_format(abs($cambioCapital), 1) }}%
                    @else
                        Sin cambio
                    @endif
                </span>
            </div>
        </div>
        <p class="text-3xl font-black text-gray-800 mb-1">L {{ number_format($capitalTotal, 2) }}</p>
        <p class="text-xs text-gray-500">vs período anterior: L {{ number_format($prevCapitalTotal, 2) }}</p>
    </div>

</div>

        {{-- MERCANCÍA COMPRADA - TARJETA ADICIONAL --}}
        <div class="rounded-3xl bg-gradient-to-br from-purple-100 to-purple-200 shadow-2xl p-8 border-4 border-purple-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-2xl text-gray-800 mb-2">🛒 Mercancía comprada</h3>
                    <p class="text-sm text-gray-600 bg-white/60 px-3 py-2 rounded-xl inline-block">
                        Total gastado en compras de mercancía
                    </p>
                </div>
                <div class="text-right">
                    <div class="bg-white/80 rounded-2xl px-4 py-2 shadow-md">
                        <span class="text-xs font-semibold text-gray-600 block mb-1">vs período anterior</span>
                        <span class="text-2xl font-black {{ $cambioCompras > 0 ? 'text-orange-700' : ($cambioCompras < 0 ? 'text-emerald-700' : 'text-gray-600') }}">
                            @if($cambioCompras > 0)
                                📈 {{ number_format(abs($cambioCompras), 1) }}%
                            @elseif($cambioCompras < 0)
                                📉 {{ number_format(abs($cambioCompras), 1) }}%
                            @else
                                ➡️ Sin cambio
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="flex-1">
                    <p class="text-lg text-gray-700 mb-2 font-semibold">Este período:</p>
                    <p class="text-5xl font-black text-purple-800 mb-3">L {{ number_format($compras, 2) }}</p>
                    <p class="text-sm text-gray-600 bg-white/50 px-3 py-2 rounded-lg inline-block">
                        Período anterior: L {{ number_format($prevCompras, 2) }}
                    </p>
                </div>

                <div class="text-center bg-white/70 rounded-2xl p-6 shadow-lg">
                    @if($cambioCompras > 0)
                        <div class="text-6xl mb-2">📈</div>
                        <p class="text-sm font-bold text-orange-700">Compraste MÁS<br>que antes</p>
                    @elseif($cambioCompras < 0)
                        <div class="text-6xl mb-2">📉</div>
                        <p class="text-sm font-bold text-emerald-700">Compraste MENOS<br>que antes</p>
                    @else
                        <div class="text-6xl mb-2">➡️</div>
                        <p class="text-sm font-bold text-gray-600">Igual que<br>antes</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- GRÁFICA: ENTRADAS VS SALIDAS POR DÍA --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Entradas vs Salidas por Día</h3>
            <canvas id="chartEntradasSalidas" height="80"></canvas>
        </div>

        {{-- ENTRADAS DETALLADAS --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 p-6">
                <h3 class="text-xl font-bold text-white">💰 ENTRADAS DE DINERO (Todo lo que entró)</h3>
                <p class="text-emerald-100 text-sm">Dinero que ingresó a tu negocio</p>
            </div>
            <div class="p-6 space-y-6">
                
                {{-- Ventas del Negocio --}}
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-sm">📊 VENTAS DEL NEGOCIO</span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="border rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">💵 Efectivo</p>
                            <p class="text-2xl font-bold text-emerald-700">L {{ number_format($ventasEfectivo, 2) }}</p>
                        </div>
                        <div class="border rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">💳 Tarjeta</p>
                            <p class="text-2xl font-bold text-emerald-700">L {{ number_format($ventasTarjeta, 2) }}</p>
                        </div>
                        <div class="border rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">🏦 Transferencia</p>
                            <p class="text-2xl font-bold text-emerald-700">L {{ number_format($ventasTransf, 2) }}</p>
                        </div>
                        <div class="border rounded-lg p-4 bg-emerald-50">
                            <p class="text-gray-600 text-sm mb-1 font-semibold">Subtotal Ventas</p>
                            <p class="text-2xl font-bold text-emerald-700">L {{ number_format($totalVentas, 2) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Abonos de Clientes --}}
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm">💰 ABONOS DE CLIENTES</span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="border rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">💵 Efectivo</p>
                            <p class="text-2xl font-bold text-blue-700">L {{ number_format($abonosEfectivo, 2) }}</p>
                        </div>
                        <div class="border rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">💳 Tarjeta</p>
                            <p class="text-2xl font-bold text-blue-700">L {{ number_format($abonosTarjeta, 2) }}</p>
                        </div>
                        <div class="border rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">🏦 Transferencia</p>
                            <p class="text-2xl font-bold text-blue-700">L {{ number_format($abonosTransferencia, 2) }}</p>
                        </div>
                        <div class="border rounded-lg p-4 bg-blue-50">
                            <p class="text-gray-600 text-sm mb-1 font-semibold">Subtotal Abonos</p>
                            <p class="text-2xl font-bold text-blue-700">L {{ number_format($abonosTotal, 2) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Otros Ingresos --}}
                @if($otrosIngresos > 0)
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-lg text-sm">✨ OTROS INGRESOS</span>
                    </h4>
                    <div class="space-y-2">
                        @foreach($otrosIngresosPorCategoria as $cat)
                            @php
                                $percentage = $otrosIngresos > 0 ? ($cat->total / $otrosIngresos) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $cat->cat }}</span>
                                    <span class="text-sm font-bold text-purple-700">L {{ number_format($cat->total, 2) }} ({{ number_format($percentage, 1) }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        <div class="border-t pt-3 mt-3 bg-purple-50 rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-800">Subtotal Otros Ingresos:</span>
                                <span class="font-bold text-purple-700 text-lg">L {{ number_format($otrosIngresos, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- TOTAL ENTRADAS --}}
                <div class="border-t-4 border-emerald-500 pt-4 bg-emerald-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-gray-800">💰 TOTAL ENTRADAS:</span>
                        <span class="text-3xl font-black text-emerald-700">L {{ number_format($totalEntradas, 2) }}</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- SALIDAS DETALLADAS --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                <h3 class="text-xl font-bold text-white">💸 SALIDAS DE DINERO (Todo lo que salió)</h3>
                <p class="text-red-100 text-sm">Dinero que gastaste en tu negocio</p>
            </div>
            <div class="p-6 space-y-6">
                
                {{-- Costo de Mercadería --}}
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-lg text-sm">📦 COSTO DE MERCADERÍA</span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">🛒 Compras a proveedores</p>
                            <p class="text-2xl font-bold text-orange-700">L {{ number_format($compras, 2) }}</p>
                        </div>
                        <div class="border rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">🗑️ Productos dañados/merma</p>
                            <p class="text-2xl font-bold text-orange-700">L {{ number_format($mermas, 2) }}</p>
                        </div>
                        <div class="border rounded-lg p-4 bg-orange-50">
                            <p class="text-gray-600 text-sm mb-1 font-semibold">Subtotal Inventario</p>
                            <p class="text-2xl font-bold text-orange-700">L {{ number_format($compras + $mermas, 2) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Gastos Operativos --}}
                @if($gastosOperativos > 0)
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-sm">🏢 GASTOS OPERATIVOS</span>
                    </h4>
                    <div class="space-y-2">
                        @foreach($gastosOperativosPorCategoria as $cat)
                            @php
                                $percentage = $gastosOperativos > 0 ? ($cat->total / $gastosOperativos) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $cat->cat }}</span>
                                    <span class="text-sm font-bold text-red-700">L {{ number_format($cat->total, 2) }} ({{ number_format($percentage, 1) }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        <div class="border-t pt-3 mt-3 bg-red-50 rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-800">Subtotal Gastos Operativos:</span>
                                <span class="font-bold text-red-700 text-lg">L {{ number_format($gastosOperativos, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- TOTAL SALIDAS --}}
                <div class="border-t-4 border-red-500 pt-4 bg-red-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-gray-800">💸 TOTAL SALIDAS:</span>
                        <span class="text-3xl font-black text-red-700">L {{ number_format($totalSalidas, 2) }}</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- TOP 5 GASTOS MÁS GRANDES --}}
        @if($top5Gastos->isNotEmpty())
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🔥 Top 5 Gastos Más Grandes</h3>
            <div class="space-y-3">
                @foreach($top5Gastos as $index => $gasto)
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <span class="text-red-700 font-bold text-lg">#{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">{{ $gasto->cat }}</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                @php
                                    $maxGasto = $top5Gastos->first()->total;
                                    $percentage = $maxGasto > 0 ? ($gasto->total / $maxGasto) * 100 : 0;
                                @endphp
                                <div class="bg-red-600 h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-red-700">L {{ number_format($gasto->total, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ANÁLISIS DE GANANCIAS --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📈 Análisis de Ganancias</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                    <span class="text-gray-700">Ventas totales:</span>
                    <span class="text-xl font-bold text-gray-800">L {{ number_format($totalVentas, 2) }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                    <span class="text-gray-700">Costo de mercadería:</span>
                    <span class="text-xl font-bold text-red-700">- L {{ number_format($compras + $mermas, 2) }}</span>
                </div>
                <div class="border-t-2 border-gray-300"></div>
                <div class="flex justify-between items-center p-4 bg-purple-50 rounded-lg">
                    <span class="text-gray-800 font-semibold">Ganancia bruta:</span>
                    <span class="text-2xl font-bold text-purple-700">L {{ number_format($gananciaBruta, 2) }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                    <span class="text-gray-700">Gastos operativos:</span>
                    <span class="text-xl font-bold text-red-700">- L {{ number_format($gastosOperativos, 2) }}</span>
                </div>
                <div class="border-t-2 border-gray-300"></div>
                <div class="flex justify-between items-center p-4 {{ $gananciaNeta >= 0 ? 'bg-emerald-50' : 'bg-red-50' }} rounded-lg">
                    <span class="text-gray-800 font-semibold">Ganancia neta:</span>
                    <span class="text-2xl font-bold {{ $gananciaNeta >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                        L {{ number_format($gananciaNeta, 2) }}
                    </span>
                </div>
                <div class="flex justify-between items-center p-4 bg-blue-50 rounded-lg">
                    <span class="text-gray-800 font-semibold">Margen de ganancia:</span>
                    <span class="text-2xl font-bold text-blue-700">{{ number_format($margenGanancia, 1) }}%</span>
                </div>
            </div>
        </div>

        {{-- BALANCE GENERAL --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl shadow-xl p-8 text-white">
            <h3 class="text-2xl font-bold mb-6">💰 BALANCE GENERAL</h3>
            <div class="space-y-4">
                {{-- ENTRADAS --}}
                <div class="flex justify-between items-center text-lg">
                    <span class="text-blue-100">💵 Total Entradas:</span>
                    <span class="font-bold">L {{ number_format($totalEntradas, 2) }}</span>
                </div>

                <div class="border-t-2 border-white/20 my-2"></div>

                {{-- SALIDAS --}}
                <div class="flex justify-between items-center text-lg">
                    <span class="text-blue-100">💸 Total Salidas:</span>
                    <span class="font-bold">L {{ number_format($totalSalidas, 2) }}</span>
                </div>

                {{-- DESGLOSE DE SALIDAS --}}
                <div class="ml-6 space-y-3 text-sm bg-white/10 rounded-lg p-4">
                    <p class="text-xs text-blue-100 font-semibold mb-2">📋 Desglose de Salidas:</p>

                    {{-- INVENTARIO --}}
                    <div class="border-b border-white/10 pb-2">
                        <p class="text-xs text-blue-100 mb-1.5">🏬 INVENTARIO</p>
                        <div class="flex justify-between items-center text-blue-50 mb-1">
                            <span class="flex items-center gap-2 pl-2">
                                <span class="w-1.5 h-1.5 bg-orange-300 rounded-full"></span>
                                Compras de mercancía
                            </span>
                            <span class="font-semibold">L {{ number_format($compras, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-blue-50">
                            <span class="flex items-center gap-2 pl-2">
                                <span class="w-1.5 h-1.5 bg-orange-400 rounded-full"></span>
                                Mermas/Productos dañados
                            </span>
                            <span class="font-semibold">L {{ number_format($mermas, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-blue-50 mt-1.5 pt-1.5 border-t border-white/5">
                            <span class="text-xs pl-2">Subtotal Inventario:</span>
                            <span class="font-bold">L {{ number_format($compras + $mermas, 2) }}</span>
                        </div>
                    </div>

                    {{-- GASTOS OPERATIVOS --}}
                    <div class="pt-1">
                        <p class="text-xs text-blue-100 mb-1.5">💼 GASTOS OPERATIVOS (Todos los egresos)</p>
                        <div class="flex justify-between items-center text-blue-50">
                            <span class="flex items-center gap-2 pl-2">
                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                                Gastos operativos totales
                            </span>
                            <span class="font-bold">L {{ number_format($gastosOperativos, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t-2 border-white/30 my-4"></div>

                {{-- BALANCE FINAL --}}
                <div class="flex justify-between items-center">
                    <span class="text-2xl font-bold">💎 BALANCE DE CAJA:</span>
                    <span class="text-5xl font-black">
                        L {{ number_format($balance, 2) }}
                        @if($balance >= 0) ✅ @else ❌ @endif
                    </span>
                </div>
                <p class="text-blue-100 text-sm mt-4">
                    💡 <strong>Inventario:</strong> Compras de mercancía del sistema de inventario.
                    <strong>Gastos Operativos:</strong> Todos los egresos registrados (luz, agua, salarios, pagos a proveedores, etc).
                </p>
            </div>
        </div>

        {{-- PROYECCIÓN DEL MES --}}
        @if($diasRestantes > 0)
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🔮 Proyección del Mes</h3>
            <p class="text-gray-600 mb-4">Si continúas a este ritmo, así terminarás el mes:</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border rounded-lg p-4 text-center">
                    <p class="text-gray-600 text-sm mb-1">Entradas proyectadas</p>
                    <p class="text-2xl font-bold text-emerald-700">L {{ number_format($proyeccionEntradas, 2) }}</p>
                </div>
                <div class="border rounded-lg p-4 text-center">
                    <p class="text-gray-600 text-sm mb-1">Salidas proyectadas</p>
                    <p class="text-2xl font-bold text-red-700">L {{ number_format($proyeccionSalidas, 2) }}</p>
                </div>
                <div class="border rounded-lg p-4 text-center {{ $proyeccionBalance >= 0 ? 'bg-emerald-50' : 'bg-red-50' }}">
                    <p class="text-gray-600 text-sm mb-1">Balance proyectado</p>
                    <p class="text-2xl font-bold {{ $proyeccionBalance >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                        L {{ number_format($proyeccionBalance, 2) }}
                    </p>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3">⏳ Faltan {{ $diasRestantes }} días para terminar el mes</p>
        </div>
        @endif

        {{-- COMPARACIÓN CON PERÍODO ANTERIOR --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Comparación con Período Anterior</h3>
            <p class="text-gray-600 mb-4">
                <span class="font-semibold">Período actual:</span> {{ Carbon\Carbon::parse($start)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($end)->format('d/m/Y') }} ({{ $diasPeriodo }} días)<br>
                <span class="font-semibold">Comparando con:</span> {{ Carbon\Carbon::parse($start)->subDays($diasPeriodo)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($end)->subDays($diasPeriodo)->format('d/m/Y') }} ({{ $diasPeriodo }} días)
            </p>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Este Período</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Anterior</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cambio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">Entradas</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-700">L {{ number_format($totalEntradas, 2) }}</td>
                            <td class="px-6 py-4 text-right text-gray-600">L {{ number_format($prevEntradas, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $cambioEntradas >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $cambioEntradas >= 0 ? '↑' : '↓' }} {{ number_format(abs($cambioEntradas), 1) }}%
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">Salidas</td>
                            <td class="px-6 py-4 text-right font-semibold text-red-700">L {{ number_format($totalSalidas, 2) }}</td>
                            <td class="px-6 py-4 text-right text-gray-600">L {{ number_format($prevSalidas, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $cambioSalidas <= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $cambioSalidas >= 0 ? '↑' : '↓' }} {{ number_format(abs($cambioSalidas), 1) }}%
                                </span>
                            </td>
                        </tr>
                        <tr class="bg-blue-50">
                            <td class="px-6 py-4 font-bold text-gray-900">Balance</td>
                            <td class="px-6 py-4 text-right font-bold text-blue-700 text-lg">L {{ number_format($balance, 2) }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-600">L {{ number_format($prevBalance, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $cambioBalance >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $cambioBalance >= 0 ? '↑' : '↓' }} {{ number_format(abs($cambioBalance), 1) }}%
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">Ganancia Bruta</td>
                            <td class="px-6 py-4 text-right font-semibold text-purple-700">L {{ number_format($gananciaBruta, 2) }}</td>
                            <td class="px-6 py-4 text-right text-gray-600">L {{ number_format($prevGananciaBruta, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $cambioGanancia >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $cambioGanancia >= 0 ? '↑' : '↓' }} {{ number_format(abs($cambioGanancia), 1) }}%
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

   @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que Chart.js esté cargado
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está cargado');
        return;
    }

    // Gráfica de Entradas vs Salidas
    const ctx = document.getElementById('chartEntradasSalidas');
    
    if (!ctx) {
        console.error('No se encontró el elemento canvas');
        return;
    }

    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($datosGrafica, 'fecha')) !!},
            datasets: [
                {
                    label: '💰 Entradas',
                    data: {!! json_encode(array_column($datosGrafica, 'entradas')) !!},
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: '💸 Salidas',
                    data: {!! json_encode(array_column($datosGrafica, 'salidas')) !!},
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14,
                            weight: 'bold'
                        },
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': L ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'L ' + value.toLocaleString('es-HN', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        },
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });

    console.log('Gráfica creada exitosamente');
});
</script>
@endpush

</x-app-layout>