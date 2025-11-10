@extends('layouts.app')

@section('title', 'Dashboard')
@php
    // Valor por defecto si no viene del controlador (evita el error)
    $range = $range ?? [
        'start' => now()->startOfMonth()->toDateString(),
        'end'   => now()->endOfMonth()->toDateString(),
        'label' => $rangeLabel ?? '',
    ];
@endphp

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- Filtros de rango --}}
  <form method="GET" class="flex flex-wrap gap-2 items-end">
    <div>
      <label class="text-sm font-medium">Rango</label>
      <select name="range" class="border rounded-lg px-3 py-2">
        @php $mode = request('range','this_month'); @endphp
        <option value="today" {{ $mode=='today'?'selected':'' }}>Hoy</option>
        <option value="yesterday" {{ $mode=='yesterday'?'selected':'' }}>Ayer</option>
        <option value="this_month" {{ $mode=='this_month'?'selected':'' }}>Este mes</option>
        <option value="last_month" {{ $mode=='last_month'?'selected':'' }}>Mes pasado</option>
        <option value="custom" {{ $mode=='custom'?'selected':'' }}>Personalizado</option>
      </select>
    </div>
    <div>
      <label class="text-sm font-medium">Desde</label>
      <input type="date" name="start" value="{{ \Illuminate\Support\Str::of($range['start'])->substr(0,10) }}" class="border rounded-lg px-3 py-2">

  
    </div>
    <div>
      <label class="text-sm font-medium">Hasta</label>
      <input type="date" name="end" value="{{ \Illuminate\Support\Str::of($range['end'])->substr(0,10) }}" class="border rounded-lg px-3 py-2">

    </div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">Aplicar</button>
    <span class="ml-3 text-sm text-gray-500">Rango: {{ $range['label'] }}</span>
  </form>




@php
    // Si por alguna razón no vinieran, usa colecciones vacías y evita el crash
    $lowStock = $lowStock ?? collect();
    $expired  = $expired  ?? collect();
    $expiring = $expiring ?? collect();

    // Ahora arma las listas con seguridad
    $listas = [
        ['t' => 'Bajo stock', 'data' => $lowStock],
        ['t' => 'Vencidos',   'data' => $expired],
        ['t' => 'Por vencer', 'data' => $expiring],
    ];
@endphp




  {{-- Fila 1: Alertas --}}
  {{-- Fila 1: Alertas --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
  <div class="rounded-xl p-4 bg-red-600 text-white shadow">
    <div class="text-sm opacity-90">🔻 Bajo stock</div>
    <div class="text-3xl font-bold">
      {{ isset($lowStock) ? $lowStock->count() : ($lowStockCount ?? 0) }}
    </div>
    <div class="text-xs mt-2">Productos por debajo del mínimo</div>
  </div>

  <div class="rounded-xl p-4 bg-gray-900 text-white shadow">
    <div class="text-sm opacity-90">⚫ Vencidos</div>
    <div class="text-3xl font-bold">
      {{ isset($expired) ? $expired->count() : ($expiredCount ?? 0) }}
    </div>
    <div class="text-xs mt-2">Fecha menor a hoy</div>
  </div>

  <div class="rounded-xl p-4 bg-amber-500 text-white shadow">
    <div class="text-sm opacity-90">⏳ Por vencer (≤30 días)</div>
    <div class="text-3xl font-bold">
      {{ isset($expiring) ? $expiring->count() : ($expiringCount ?? 0) }}
    </div>
    <div class="text-xs mt-2">Urgente revisar rotación</div>
  </div>
</div>










  {{-- Listas de detalle --}}
 @php $listas = [
  ['t' => 'Bajo stock', 'data' => $lowStock],
  ['t' => 'Vencidos',   'data' => $expired],
  ['t' => 'Por vencer', 'data' => $expiring],
]; @endphp

@foreach ($listas as $L)
  <div class="rounded-xl bg-white shadow">
    <div class="px-4 py-3 border-b font-semibold">{{ $L['t'] }}</div>
    <div class="max-h-64 overflow-auto divide-y">
      @forelse ($L['data'] as $it)
        <div class="px-4 py-2 text-sm">
          <div class="font-medium">{{ $it->name }}</div>
          <div class="text-gray-500">
            @isset($it->stock) Stock: {{ $it->stock }} @endisset
            @isset($it->min_stock) · Min: {{ $it->min_stock }} @endisset
            @isset($it->expires_at) · Vence: {{ $it->expires_at }} @endisset
          </div>
        </div>
      @empty
        <div class="px-4 py-3 text-sm text-gray-500">Sin datos</div>
      @endforelse
    </div>
  </div>
@endforeach





<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
  <!-- KPI Ventas -->
  <div class="bg-white rounded-xl shadow p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="text-2xl">🧾</div>
      <div>
        <div class="text-gray-500 text-sm">Ventas (tickets)</div>
        <div id="kpi-ventas" class="text-2xl font-semibold">—</div>
      </div>
    </div>
    <div id="kpi-ventas-delta" class="text-sm font-medium">—</div>
  </div>

  <!-- KPI Unidades -->
  <div class="bg-white rounded-xl shadow p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="text-2xl">📦</div>
      <div>
        <div class="text-gray-500 text-sm">Unidades vendidas</div>
        <div id="kpi-unidades" class="text-2xl font-semibold">—</div>
      </div>
    </div>
    <div id="kpi-unidades-delta" class="text-sm font-medium">—</div>
  </div>
</div>



  {{-- Fila 2: Comparaciones y Horas pico --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl bg-white shadow p-4">
      <div class="flex items-baseline justify-between">
        <h3 class="font-semibold">Ventas del rango</h3>
  @php
  $curr = data_get($compare ?? null, 'current', 0);
  $prev = data_get($compare ?? null, 'previous', 0);
  $diff = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : 0;
@endphp

<span class="{{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }} text-sm font-semibold">
  {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 1) }}%
</span>


      </div>
      <div id="chartMonthly" class="mt-2 h-64"></div>
    </div>

    <div class="rounded-xl bg-white shadow p-4">
      <h3 class="font-semibold">⏰ Horas pico ({{ $range['label'] }})</h3>
      <div id="chartHourly" class="mt-2 h-64"></div>
    </div>
  </div>



<div class="bg-white rounded-xl shadow p-4 mb-6">
  <div class="flex items-center justify-between mb-2">
    <h3 class="font-semibold text-gray-800">🗺️ Mapa de calor — Ventas por hora y día</h3>
    <span class="text-sm text-gray-500">{{ $rangeLabel ?? '' }}</span>
  </div>
  <div id="chart-heatmap" style="height: 380px;"></div>
</div>

<div class="rounded-xl bg-white shadow p-4 mt-6">
  <div class="flex items-baseline justify-between">
    <h3 class="font-semibold">Días más vendidos</h3>
    <div class="space-x-1 text-sm">
      <button id="btnTickets" class="px-2 py-1 rounded border">Tickets</button>
      <button id="btnAmount"  class="px-2 py-1 rounded border">Monto</button>
      <button id="btnUnits"   class="px-2 py-1 rounded border">Unidades</button>
    </div>
  </div>
  <p class="text-xs text-gray-500 mb-2">Qué días se vende más (elige: cantidad de ventas, monto o unidades).</p>
  <div id="chartByDay" style="height: 320px;"></div>
</div>

{{-- payload para JS --}}
@php
  $sbd = $salesByDay ?? ['labels'=>[],'tickets'=>[],'amount'=>[],'units'=>[]];
@endphp
<script>
  window.DASH = window.DASH || {};
  window.DASH.salesByDay = @json($sbd);
</script>




  {{-- Fila 3: Movimientos y márgenes --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="rounded-xl bg-white shadow p-4">
      <h3 class="font-semibold">🔥 Más vendidos</h3>
      <div id="chartTopMovers" class="mt-2 h-72"></div>
    </div>
    <div class="rounded-xl bg-white shadow p-4">
      <h3 class="font-semibold">🧊 Menos movimiento</h3>
      <div id="chartSlowMovers" class="mt-2 h-72"></div>
    </div>
    <div class="rounded-xl bg-white shadow p-4">
      <h3 class="font-semibold">💚 Mayor margen</h3>
      <div id="chartMargins" class="mt-2 h-72"></div>
    </div>
  </div>





{{-- Productos que mandan (Pareto / ABC) --}}
<div class="bg-white rounded-xl shadow p-4 mb-6">
  <div class="flex items-center justify-between mb-2">
    <h3 class="font-semibold text-gray-800">🏆 Productos que mandan (Pareto / ABC)</h3>
    <span class="text-sm text-gray-500">{{ $range['label'] ?? '' }}
</span>
  </div>
  <p class="text-sm text-gray-600 mb-3">Muestra qué productos explican la mayor parte de tus ventas (en unidades). A=Top, B=Medio, C=Bajo.</p>
  <div id="chart-abc" style="height: 380px;"></div>
</div>


@php
    // $abcChart puede venir como array de arrays u objetos; lo normalizamos
    $abc = collect($abcChart ?? [])->map(function ($r) {
        return [
            'name'      => is_array($r) ? ($r['name'] ?? '') : ($r->name ?? ''),
            'unidades'  => (int)(is_array($r) ? ($r['unidades'] ?? 0) : ($r->unidades ?? 0)),
            'pct'       => (float)(is_array($r) ? ($r['pct'] ?? 0) : ($r->pct ?? 0)),
            'acum'      => (float)(is_array($r) ? ($r['acum'] ?? 0) : ($r->acum ?? 0)),
            'class'     => is_array($r) ? ($r['class'] ?? 'C') : ($r->class ?? 'C'),
        ];
    });

    $groups = [
        'A' => $abc->where('class','A')->values(),
        'B' => $abc->where('class','B')->values(),
        'C' => $abc->where('class','C')->values(),
    ];
@endphp

<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach (['A' => 'Top (≈80%)', 'B' => 'Medio (≈15%)', 'C' => 'Bajo (≈5%)'] as $clase => $desc)
        <div class="rounded-xl border bg-white p-4">
            <div class="flex items-baseline justify-between">
                <h4 class="font-semibold">Clase {{ $clase }}</h4>
                <span class="text-xs text-gray-500">{{ $groups[$clase]->count() }} productos</span>
            </div>

            @if ($groups[$clase]->isEmpty())
                <p class="text-sm text-gray-400 mt-2">Sin productos en esta clase.</p>
            @else
                <ol class="mt-2 text-sm space-y-1 max-h-48 overflow-auto pr-1">
                    @foreach ($groups[$clase] as $item)
                        <li class="flex items-center justify-between gap-2">
                            <span class="truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                            <span class="shrink-0 tabular-nums text-gray-600">
                                {{ number_format($item['pct'],1) }}% · {{ $item['unidades'] }} uds
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif

            <p class="text-xs text-gray-400 mt-3">{{ $desc }}</p>
        </div>
    @endforeach
</div>











{{-- Estrellas (margen × rotación) --}}
<div class="bg-white rounded-xl shadow p-4 mb-6">
  <div class="flex items-center justify-between mb-2">
    <h3 class="font-semibold text-gray-800">⭐ Estrellas (margen × rotación)</h3>
    <span class="text-sm text-gray-500">{{ $range['label'] ?? '' }}
</span>
  </div>
  <p class="text-sm text-gray-600 mb-3">Mezcla cuánto se vende (unidades) con qué tanto ganás por unidad (margen%).</p>
  <div id="chart-stars" style="height: 380px;"></div>
</div>








  {{-- Fila 4: Métodos de pago y proveedores --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl bg-white shadow p-4">
      <h3 class="font-semibold">💳 Métodos de pago (porcentaje)</h3>
      <div id="chartPayments" class="mt-2 h-64"></div>
    </div>
    <div class="rounded-xl bg-white shadow p-4">
      <h3 class="font-semibold">🚚 Proveedores que más llegan</h3>
      <div id="chartProviders" class="mt-2 h-64"></div>
    </div>
  </div>

</div>







{{-- Datos para JS --}}
<script>
  window.DASH = window.DASH || {};
  Object.assign(window.DASH, {!! json_encode([
    'salesByMonth' => $salesByMonth ?? [],
    'hourly'       => $hourly ?? [],
    'topMovers'    => $topMovers ?? [],
    'slowMovers'   => $slowMovers ?? [],
    'margins'      => $marginProducts ?? [],
    'paymentShare' => $paymentShare ?? [],
    'providersTop' => $providersTop ?? [],
    'kpis'         => $kpis ?? [],
    'heatmap'      => $heatmap ?? [],
    'abcChart'     => $abcChart ?? [],
    'starsChart'   => $starsChart ?? [],
    'salesByDay'   => $salesByDay ?? [],
  ]) !!});
</script>



@endsection
