@extends('layouts.app')

@section('title', 'Inicio')
@php
    $range = $range ?? [
        'start' => now()->startOfMonth()->toDateString(),
        'end'   => now()->endOfMonth()->toDateString(),
        'label' => $rangeLabel ?? '',
    ];
@endphp

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 space-y-8">

  {{-- FILTROS DE FECHA - MÁS GRANDES Y COLORIDOS --}}
  <form method="GET" class="bg-gradient-to-r from-blue-100 to-purple-100 rounded-3xl p-6 shadow-xl border-4 border-purple-200">
    <div class="flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="text-lg font-bold text-gray-800 mb-2 block">📅 Periodo</label>
        <select name="range" class="w-full border-2 border-purple-300 rounded-xl px-4 py-3 text-base font-semibold focus:border-purple-500 focus:ring-4 focus:ring-purple-200">
          @php $mode = request('range','this_month'); @endphp
          <option value="today" {{ $mode=='today'?'selected':'' }}>Hoy</option>
          <option value="yesterday" {{ $mode=='yesterday'?'selected':'' }}>Ayer</option>
          <option value="this_month" {{ $mode=='this_month'?'selected':'' }}>Este mes</option>
          <option value="last_month" {{ $mode=='last_month'?'selected':'' }}>Mes pasado</option>
          <option value="custom" {{ $mode=='custom'?'selected':'' }}>Personalizado</option>
        </select>
      </div>
      <div class="flex-1 min-w-[180px]">
        <label class="text-lg font-bold text-gray-800 mb-2 block">Desde</label>
        <input type="date" name="start" value="{{ \Illuminate\Support\Str::of($range['start'])->substr(0,10) }}"
               class="w-full border-2 border-purple-300 rounded-xl px-4 py-3 text-base font-semibold focus:border-purple-500 focus:ring-4 focus:ring-purple-200">
      </div>
      <div class="flex-1 min-w-[180px]">
        <label class="text-lg font-bold text-gray-800 mb-2 block">Hasta</label>
        <input type="date" name="end" value="{{ \Illuminate\Support\Str::of($range['end'])->substr(0,10) }}"
               class="w-full border-2 border-purple-300 rounded-xl px-4 py-3 text-base font-semibold focus:border-purple-500 focus:ring-4 focus:ring-purple-200">
      </div>
      <button class="bg-gradient-to-r from-purple-400 to-purple-500 hover:from-purple-500 hover:to-purple-600 text-white px-8 py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all">
        Aplicar
      </button>
    </div>
    <div class="mt-4 text-center">
      <span class="inline-block bg-white/80 px-6 py-3 rounded-2xl text-lg font-bold text-gray-700 shadow-md">
        📊 Mostrando: {{ $range['label'] }}
      </span>
    </div>
  </form>

  @php
    $lowStock = $lowStock ?? collect();
    $expired  = $expired  ?? collect();
    $expiring = $expiring ?? collect();
  @endphp

  {{-- ALERTAS DE PRODUCTOS - MÁS COLORIDAS --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="rounded-3xl p-8 bg-gradient-to-br from-red-300 to-red-400 text-white shadow-2xl border-4 border-red-500 hover:scale-105 transition-transform">
      <div class="text-2xl mb-2">🔻</div>
      <div class="text-lg font-bold mb-1">Productos con poco stock</div>
      <div class="text-5xl font-bold mb-2">
        {{ isset($lowStock) ? $lowStock->count() : ($lowStockCount ?? 0) }}
      </div>
      <div class="text-base opacity-90">Hay que pedir más</div>
    </div>

    <div class="rounded-3xl p-8 bg-gradient-to-br from-gray-700 to-gray-800 text-white shadow-2xl border-4 border-gray-900 hover:scale-105 transition-transform">
      <div class="text-2xl mb-2">⚫</div>
      <div class="text-lg font-bold mb-1">Productos vencidos</div>
      <div class="text-5xl font-bold mb-2">
        {{ isset($expired) ? $expired->count() : ($expiredCount ?? 0) }}
      </div>
      <div class="text-base opacity-90">Ya no se pueden vender</div>
    </div>

    <div class="rounded-3xl p-8 bg-gradient-to-br from-amber-300 to-amber-400 text-white shadow-2xl border-4 border-amber-500 hover:scale-105 transition-transform">
      <div class="text-2xl mb-2">⏳</div>
      <div class="text-lg font-bold mb-1">Por vencer pronto</div>
      <div class="text-5xl font-bold mb-2">
        {{ isset($expiring) ? $expiring->count() : ($expiringCount ?? 0) }}
      </div>
      <div class="text-base opacity-90">Vencen en 30 días o menos</div>
    </div>
  </div>

  
  {{-- LISTAS DE PRODUCTOS CON PROBLEMAS --}}
  @php $listas = [
    ['t' => '🔻 Productos con poco stock', 'data' => $lowStock, 'bg' => 'from-red-50 to-red-100', 'border' => 'border-red-300'],
    ['t' => '⚫ Productos vencidos', 'data' => $expired, 'bg' => 'from-gray-100 to-gray-200', 'border' => 'border-gray-400'],
    ['t' => '⏳ Por vencer pronto (30 días)', 'data' => $expiring, 'bg' => 'from-amber-50 to-amber-100', 'border' => 'border-amber-300'],
  ]; @endphp

  @foreach ($listas as $L)
    <div class="rounded-3xl bg-gradient-to-br {{ $L['bg'] }} shadow-xl border-4 {{ $L['border'] }} overflow-hidden">
      <div class="px-6 py-4 border-b-2 {{ $L['border'] }} font-bold text-xl text-gray-800">{{ $L['t'] }}</div>
      <div class="max-h-80 overflow-auto divide-y">
        @forelse ($L['data'] as $it)
          <div class="px-6 py-4">
            <div class="font-bold text-lg text-gray-800">{{ $it->name }}</div>
            <div class="text-gray-600 text-base mt-1">
              @isset($it->stock) 📦 Quedan: {{ $it->stock }} @endisset
              @isset($it->min_stock) · Mínimo: {{ $it->min_stock }} @endisset
              @isset($it->expires_at) · ⏰ Vence: {{ $it->expires_at }} @endisset
            </div>
          </div>
        @empty
          <div class="px-6 py-4 text-base text-gray-500">✅ ¡Todo bien! No hay problemas aquí</div>
        @endforelse
      </div>
    </div>
  @endforeach






{{-- CALENDARIO DE NOTAS --}}
  <div class="bg-gradient-to-br from-indigo-100 to-purple-100 rounded-3xl shadow-2xl p-6 border-4 border-indigo-300">
    <div class="flex items-center justify-between mb-6">
      <h3 class="font-bold text-2xl text-gray-800">📅 Calendario y Notas</h3>
      <div class="flex items-center gap-2">
        <button id="calendarPrevYear" class="px-4 py-2 bg-white/80 hover:bg-white rounded-xl font-bold text-gray-700 shadow-md hover:shadow-lg transition-all">◀</button>
        <span id="calendarCurrentYear" class="text-xl font-bold text-gray-800 px-4"></span>
        <button id="calendarNextYear" class="px-4 py-2 bg-white/80 hover:bg-white rounded-xl font-bold text-gray-700 shadow-md hover:shadow-lg transition-all">▶</button>
      </div>
    </div>

    {{-- Vista de 12 meses --}}
    <div id="calendarYearView" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>

    {{-- Vista de un mes completo --}}
    <div id="calendarMonthView" class="hidden">
      <div class="flex items-center justify-between mb-4">
        <button id="calendarBackToYear" class="px-4 py-2 bg-white/80 hover:bg-white rounded-xl font-bold text-gray-700 shadow-md hover:shadow-lg transition-all">◀ Volver</button>
        <h4 id="calendarMonthTitle" class="text-xl font-bold text-gray-800"></h4>
      </div>
      <div id="calendarDaysGrid" class="grid grid-cols-7 gap-2"></div>
    </div>
  </div>

  {{-- Modal para editar nota del día --}}
  <div id="noteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-2xl w-full border-4 border-indigo-300">
      <div class="flex items-center justify-between mb-6">
        <h3 id="noteModalTitle" class="text-2xl font-bold text-gray-800"></h3>
        <button id="noteModalClose" class="text-gray-400 hover:text-gray-600 text-3xl font-bold">&times;</button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-lg font-bold text-gray-700 mb-2">📝 Nota</label>
          <textarea id="noteContent" rows="6" class="w-full border-2 border-indigo-300 rounded-xl px-4 py-3 text-base focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200" placeholder="Escribe tu nota aquí..."></textarea>
        </div>

        <div>
          <label class="block text-lg font-bold text-gray-700 mb-2">🎨 Prioridad</label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <button class="priority-btn px-4 py-3 rounded-xl font-bold text-white shadow-md hover:shadow-lg transition-all" data-priority="low" style="background-color: #10b981;">
              🟢 Baja
            </button>
            <button class="priority-btn px-4 py-3 rounded-xl font-bold text-white shadow-md hover:shadow-lg transition-all" data-priority="normal" style="background-color: #3b82f6;">
              🔵 Normal
            </button>
            <button class="priority-btn px-4 py-3 rounded-xl font-bold text-white shadow-md hover:shadow-lg transition-all" data-priority="important" style="background-color: #f59e0b;">
              🟡 Importante
            </button>
            <button class="priority-btn px-4 py-3 rounded-xl font-bold text-white shadow-md hover:shadow-lg transition-all" data-priority="urgent" style="background-color: #ef4444;">
              🔴 Urgente
            </button>
          </div>
        </div>

        <div id="noteLastUpdated" class="text-sm text-gray-500 italic"></div>

        <div class="flex gap-3 pt-4">
          <button id="noteSaveBtn" class="flex-1 bg-gradient-to-r from-indigo-400 to-indigo-500 hover:from-indigo-500 hover:to-indigo-600 text-white px-6 py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all">
            💾 Guardar
          </button>
          <button id="noteDeleteBtn" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all">
            🗑️ Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>






  {{-- VENTAS Y UNIDADES VENDIDAS --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-3xl shadow-2xl p-8 border-4 border-green-300">
      <div class="flex items-center gap-4 mb-4">
        <div class="text-6xl">🧾</div>
        <div>
          <div class="text-gray-600 text-lg font-semibold">Ventas realizadas</div>
          <div id="kpi-ventas" class="text-5xl font-bold text-gray-800">—</div>
        </div>
      </div>
      <div id="kpi-ventas-delta" class="text-xl font-bold">—</div>
    </div>

    <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-3xl shadow-2xl p-8 border-4 border-blue-300">
      <div class="flex items-center gap-4 mb-4">
        <div class="text-6xl">📦</div>
        <div>
          <div class="text-gray-600 text-lg font-semibold">Productos vendidos</div>
          <div id="kpi-unidades" class="text-5xl font-bold text-gray-800">—</div>
        </div>
      </div>
      <div id="kpi-unidades-delta" class="text-xl font-bold">—</div>
    </div>
  </div>

  {{-- COMPARACIÓN VENTAS Y HORAS PICO --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-3xl bg-gradient-to-br from-purple-100 to-purple-200 shadow-2xl p-6 border-4 border-purple-300">
      <div class="flex items-baseline justify-between mb-4">
        <h3 class="font-bold text-2xl text-gray-800">💰 Dinero de ventas</h3>
        @php
          $curr = data_get($compare ?? null, 'current', 0);
          $prev = data_get($compare ?? null, 'previous', 0);
          $diff = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : 0;
        @endphp
        <span class="{{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }} text-xl font-bold px-4 py-2 rounded-xl bg-white/80">
          {{ $diff >= 0 ? '📈' : '📉' }} {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 1) }}%
        </span>
      </div>
      <div id="chartMonthly" class="h-72"></div>
    </div>

    <div class="rounded-3xl bg-gradient-to-br from-orange-100 to-orange-200 shadow-2xl p-6 border-4 border-orange-300">
      <h3 class="font-bold text-2xl text-gray-800 mb-4">⏰ Horas con más ventas</h3>
      <div id="chartHourly" class="h-72"></div>
    </div>
  </div>

  {{-- MAPA DE CALOR --}}
  <div class="bg-gradient-to-br from-pink-100 to-pink-200 rounded-3xl shadow-2xl p-6 border-4 border-pink-300">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-2xl text-gray-800">🗺️ ¿Cuándo se vende más?</h3>
      <span class="text-lg text-gray-600 bg-white/80 px-4 py-2 rounded-xl font-semibold">{{ $rangeLabel ?? '' }}</span>
    </div>
    <div id="chart-heatmap" style="height: 400px;"></div>
  </div>

  {{-- DÍAS MÁS VENDIDOS --}}
  <div class="rounded-3xl bg-gradient-to-br from-yellow-100 to-yellow-200 shadow-2xl p-6 border-4 border-yellow-300">
    <div class="flex items-baseline justify-between mb-4">
      <h3 class="font-bold text-2xl text-gray-800">📅 ¿Qué días se vende más?</h3>
      <div class="space-x-2">
        <button id="btnTickets" class="px-4 py-2 rounded-xl border-2 text-base font-semibold hover:bg-white/80 transition-all">Ventas</button>
        <button id="btnAmount" class="px-4 py-2 rounded-xl border-2 text-base font-semibold hover:bg-white/80 transition-all">Dinero</button>
        <button id="btnUnits" class="px-4 py-2 rounded-xl border-2 text-base font-semibold hover:bg-white/80 transition-all">Productos</button>
      </div>
    </div>
    <p class="text-base text-gray-700 mb-4 bg-white/60 px-4 py-2 rounded-xl">Lunes, Martes, Miércoles... ¿Cuál es el día que más vendemos?</p>
    <div id="chartByDay" style="height: 340px;"></div>
  </div>

  {{-- PRODUCTOS MÁS Y MENOS VENDIDOS --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="rounded-3xl bg-gradient-to-br from-green-100 to-green-200 shadow-2xl p-6 border-4 border-green-300">
      <h3 class="font-bold text-2xl text-gray-800 mb-4">🔥 Los que más se venden</h3>
      <div id="chartTopMovers" class="h-80"></div>
    </div>
    <div class="rounded-3xl bg-gradient-to-br from-blue-100 to-blue-200 shadow-2xl p-6 border-4 border-blue-300">
      <h3 class="font-bold text-2xl text-gray-800 mb-4">🧊 Los que NO se venden</h3>
      <div id="chartSlowMovers" class="h-80"></div>
    </div>
    <div class="rounded-3xl bg-gradient-to-br from-purple-100 to-purple-200 shadow-2xl p-6 border-4 border-purple-300">
      <h3 class="font-bold text-2xl text-gray-800 mb-4">💚 Los que más ganancia dejan</h3>
      <div id="chartMargins" class="h-80"></div>
    </div>
  </div>

  {{-- ANÁLISIS ABC --}}
  <div class="bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-3xl shadow-2xl p-6 border-4 border-indigo-300">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-2xl text-gray-800">🏆 ¿Qué productos son los más importantes?</h3>
      <span class="text-lg text-gray-600 bg-white/80 px-4 py-2 rounded-xl font-semibold">{{ $range['label'] ?? '' }}</span>
    </div>
    <p class="text-base text-gray-700 mb-4 bg-white/60 px-4 py-3 rounded-xl">
      <strong>Los TOP (A)</strong> son los que generan el 80% de las ventas. <strong>Los Medios (B)</strong> el 15%. <strong>Los Bajos (C)</strong> el 5%.
    </p>
    <div id="chart-abc" style="height: 400px;"></div>
  </div>

  @php
    $abc = collect($abcChart ?? [])->map(function ($r) {
        return [
            'name' => is_array($r) ? ($r['name'] ?? '') : ($r->name ?? ''),
            'unidades' => (int)(is_array($r) ? ($r['unidades'] ?? 0) : ($r->unidades ?? 0)),
            'pct' => (float)(is_array($r) ? ($r['pct'] ?? 0) : ($r->pct ?? 0)),
            'acum' => (float)(is_array($r) ? ($r['acum'] ?? 0) : ($r->acum ?? 0)),
            'class' => is_array($r) ? ($r['class'] ?? 'C') : ($r->class ?? 'C'),
        ];
    });

    $groups = [
        'A' => $abc->where('class','A')->values(),
        'B' => $abc->where('class','B')->values(),
        'C' => $abc->where('class','C')->values(),
    ];
  @endphp

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach ([
      'A' => ['title' => '🥇 Los TOP', 'desc' => 'Generan el 80% de ventas', 'bg' => 'from-yellow-100 to-yellow-200', 'border' => 'border-yellow-400'],
      'B' => ['title' => '🥈 Los Medios', 'desc' => 'Generan el 15% de ventas', 'bg' => 'from-gray-100 to-gray-200', 'border' => 'border-gray-400'],
      'C' => ['title' => '🥉 Los Bajos', 'desc' => 'Generan el 5% de ventas', 'bg' => 'from-orange-100 to-orange-200', 'border' => 'border-orange-400'],
    ] as $clase => $info)
      <div class="rounded-3xl border-4 {{ $info['border'] }} bg-gradient-to-br {{ $info['bg'] }} p-6 shadow-xl">
        <div class="flex items-baseline justify-between mb-3">
          <h4 class="font-bold text-xl text-gray-800">{{ $info['title'] }}</h4>
          <span class="text-sm text-gray-600 bg-white/60 px-3 py-1 rounded-xl font-semibold">{{ $groups[$clase]->count() }} productos</span>
        </div>

        @if ($groups[$clase]->isEmpty())
          <p class="text-base text-gray-500 mt-3">No hay productos en esta categoría</p>
        @else
          <ol class="mt-3 text-base space-y-2 max-h-56 overflow-auto pr-2">
            @foreach ($groups[$clase] as $item)
              <li class="flex items-center justify-between gap-2 bg-white/60 px-3 py-2 rounded-xl">
                <span class="truncate font-semibold text-gray-800" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                <span class="shrink-0 tabular-nums text-gray-700 font-bold">
                  {{ number_format($item['pct'],1) }}%
                </span>
              </li>
            @endforeach
          </ol>
        @endif

        <p class="text-sm text-gray-600 mt-4 bg-white/60 px-3 py-2 rounded-xl font-semibold">{{ $info['desc'] }}</p>
      </div>
    @endforeach
  </div>

  {{-- ESTRELLAS (MARGEN X ROTACIÓN) --}}
  <div class="bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-3xl shadow-2xl p-6 border-4 border-cyan-300">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-2xl text-gray-800">⭐ Productos estrella</h3>
      <span class="text-lg text-gray-600 bg-white/80 px-4 py-2 rounded-xl font-semibold">{{ $range['label'] ?? '' }}</span>
    </div>
    <p class="text-base text-gray-700 mb-4 bg-white/60 px-4 py-3 rounded-xl">
      Productos que <strong>se venden mucho</strong> Y además <strong>dejan buena ganancia</strong>
    </p>
    <div id="chart-stars" style="height: 400px;"></div>
  </div>

  {{-- MÉTODOS DE PAGO --}}
  <div class="rounded-3xl bg-gradient-to-br from-teal-100 to-teal-200 shadow-2xl p-6 border-4 border-teal-300">
    <h3 class="font-bold text-2xl text-gray-800 mb-4">💳 ¿Cómo pagan los clientes?</h3>
    <div id="chartPayments" class="h-72"></div>
  </div>

</div>

{{-- Datos para JavaScript --}}
@php
  $sbd = $salesByDay ?? ['labels'=>[],'tickets'=>[],'amount'=>[],'units'=>[]];
@endphp
<script>
  window.DASH = window.DASH || {};
  Object.assign(window.DASH, {!! json_encode([
    'salesByMonth' => $salesByMonth ?? [],
    'hourly' => $hourly ?? [],
    'topMovers' => $topMovers ?? [],
    'slowMovers' => $slowMovers ?? [],
    'margins' => $marginProducts ?? [],
    'paymentShare' => $paymentShare ?? [],
    'kpis' => $kpis ?? [],
    'heatmap' => $heatmap ?? [],
    'abcChart' => $abcChart ?? [],
    'starsChart' => $starsChart ?? [],
    'salesByDay' => $sbd,
  ]) !!});
</script>

@endsection
