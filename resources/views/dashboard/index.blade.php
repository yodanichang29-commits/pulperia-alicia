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





{{-- CALENDARIO CON NOTAS --}}
<div class="bg-gradient-to-br from-cyan-100 to-blue-200 rounded-3xl shadow-2xl p-6 border-4 border-cyan-300">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-2xl text-gray-800">📅 Calendario de Notas</h3>
    <div class="flex items-center gap-2">
      <button onclick="cambiarMes(-1)" class="px-3 py-2 rounded-xl bg-white/80 hover:bg-white font-semibold">◀</button>
      <span id="mesActual" class="text-lg font-bold px-4">Mes Actual</span>
      <button onclick="cambiarMes(1)" class="px-3 py-2 rounded-xl bg-white/80 hover:bg-white font-semibold">▶</button>
    </div>
  </div>
  
  <div class="bg-white/60 rounded-2xl p-4">
    <!-- Días de la semana -->
    <div class="grid grid-cols-7 gap-2 mb-2">
      <div class="text-center font-bold text-gray-700 py-2">Lun</div>
      <div class="text-center font-bold text-gray-700 py-2">Mar</div>
      <div class="text-center font-bold text-gray-700 py-2">Mié</div>
      <div class="text-center font-bold text-gray-700 py-2">Jue</div>
      <div class="text-center font-bold text-gray-700 py-2">Vie</div>
      <div class="text-center font-bold text-gray-700 py-2">Sáb</div>
      <div class="text-center font-bold text-gray-700 py-2">Dom</div>
    </div>
    
    <!-- Días del mes -->
    <div id="calendarioDias" class="grid grid-cols-7 gap-2">
      <!-- Se llena con JavaScript -->
    </div>
  </div>
  
  <!-- Leyenda de colores -->
  <div class="mt-4 flex flex-wrap gap-3 justify-center">
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded bg-green-400"></div>
      <span class="text-sm font-semibold">Baja prioridad</span>
    </div>
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded bg-yellow-400"></div>
      <span class="text-sm font-semibold">Media prioridad</span>
    </div>
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded bg-orange-400"></div>
      <span class="text-sm font-semibold">Alta prioridad</span>
    </div>
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded bg-red-500"></div>
      <span class="text-sm font-semibold">Urgente</span>
    </div>
  </div>
</div>

<!-- Modal para agregar/editar notas -->
<div id="modalNota" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
    <h3 id="tituloModal" class="text-xl font-bold mb-4">Agregar Nota</h3>
    
    <input type="hidden" id="notaId">
    <input type="hidden" id="notaFecha">
    
    <div class="mb-4">
      <label class="block text-sm font-semibold mb-2">Fecha:</label>
      <p id="fechaMostrar" class="text-gray-700 font-medium"></p>
    </div>
    
    <div class="mb-4">
      <label class="block text-sm font-semibold mb-2">Nota:</label>
      <textarea id="notaTexto" rows="4" class="w-full border-2 rounded-xl px-3 py-2" placeholder="Escribe tu nota aquí..."></textarea>
    </div>
    
    <div class="mb-4">
      <label class="block text-sm font-semibold mb-2">Prioridad:</label>
      <select id="notaPrioridad" class="w-full border-2 rounded-xl px-3 py-2">
        <option value="low">🟢 Baja</option>
        <option value="medium">🟡 Media</option>
        <option value="high">🟠 Alta</option>
        <option value="urgent">🔴 Urgente</option>
      </select>
    </div>
    
    <div class="flex gap-2">
      <button onclick="guardarNota()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded-xl">
        Guardar
      </button>
      <button onclick="cerrarModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 rounded-xl">
        Cancelar
      </button>
      <button id="btnEliminar" onclick="eliminarNota()" class="hidden bg-red-500 hover:bg-red-600 text-white font-bold px-4 py-2 rounded-xl">
        🗑️
      </button>
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




<script>
// Variables globales del calendario
let mesActual = new Date().getMonth();
let añoActual = new Date().getFullYear();
let notasDelMes = {};

// Colores según prioridad
const coloresPrioridad = {
    'low': 'bg-green-400 border-green-500',
    'medium': 'bg-yellow-400 border-yellow-500',
    'high': 'bg-orange-400 border-orange-500',
    'urgent': 'bg-red-500 border-red-600'
};

// Cargar calendario al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarCalendario();
});

// Cambiar de mes
function cambiarMes(direccion) {
    mesActual += direccion;
    if (mesActual > 11) {
        mesActual = 0;
        añoActual++;
    } else if (mesActual < 0) {
        mesActual = 11;
        añoActual--;
    }
    cargarCalendario();
}

// Cargar el calendario
function cargarCalendario() {
    const nombresMeses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    document.getElementById('mesActual').textContent = 
        nombresMeses[mesActual] + ' ' + añoActual;
    
    // Cargar notas del servidor
    fetch(`/calendar/notes?year=${añoActual}&month=${mesActual + 1}`)
        .then(res => res.json())
        .then(data => {
            notasDelMes = data;
            generarDiasCalendario();
        })
        .catch(err => {
            console.error('Error cargando notas:', err);
            generarDiasCalendario();
        });
}

// Generar los días del mes
function generarDiasCalendario() {
    const primerDia = new Date(añoActual, mesActual, 1);
    const ultimoDia = new Date(añoActual, mesActual + 1, 0);
    const diasEnMes = ultimoDia.getDate();
    
    // Ajustar el día de la semana (Lunes = 0)
    let diaSemana = primerDia.getDay() - 1;
    if (diaSemana < 0) diaSemana = 6;
    
    const contenedor = document.getElementById('calendarioDias');
    contenedor.innerHTML = '';
    
    // Espacios vacíos antes del primer día
    for (let i = 0; i < diaSemana; i++) {
        contenedor.innerHTML += '<div></div>';
    }
    
    // Día de hoy para comparar
    const hoy = new Date();
    const hoyDia = hoy.getDate();
    const hoyMes = hoy.getMonth();
    const hoyAño = hoy.getFullYear();
    
    // Generar cada día
    for (let dia = 1; dia <= diasEnMes; dia++) {
        const fecha = `${añoActual}-${String(mesActual + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        const tieneNota = notasDelMes[fecha] && notasDelMes[fecha].length > 0;
        
        // Verificar si es hoy (solo si es el mismo día, mes y año)
        const esHoy = (dia === hoyDia && mesActual === hoyMes && añoActual === hoyAño);
        
        let colorClase = '';
        let indicadorNota = '';
        
        if (tieneNota) {
            const prioridad = notasDelMes[fecha][0].priority;
            colorClase = coloresPrioridad[prioridad];
            const numNotas = notasDelMes[fecha].length;
            indicadorNota = `<div class="absolute top-1 right-1 ${colorClase} rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold text-white">${numNotas}</div>`;
        }
        
        const borderHoy = esHoy ? 'border-4 border-blue-600' : 'border-2 border-gray-200';
        const fondoBase = tieneNota ? colorClase : 'bg-white hover:bg-blue-50';
        
        contenedor.innerHTML += `
            <div onclick="abrirModalNota('${fecha}')" 
                 class="relative min-h-[70px] rounded-xl ${borderHoy} ${fondoBase} p-2 cursor-pointer transition-all hover:scale-105 hover:shadow-lg">
                <div class="font-bold text-gray-800">${dia}</div>
                ${indicadorNota}
                ${tieneNota ? '<div class="text-xs mt-1 text-gray-700 truncate font-semibold">' + notasDelMes[fecha][0].note + '</div>' : ''}
            </div>
        `;
    }
}

// Abrir modal para agregar/editar nota (FUNCIONA PARA CUALQUIER DÍA)
function abrirModalNota(fecha) {
    document.getElementById('notaFecha').value = fecha;
    document.getElementById('notaId').value = '';
    document.getElementById('fechaMostrar').textContent = formatearFecha(fecha);
    document.getElementById('notaTexto').value = '';
    document.getElementById('notaPrioridad').value = 'medium';
    document.getElementById('btnEliminar').classList.add('hidden');
    
    // Si ya existe una nota, cargarla
    if (notasDelMes[fecha] && notasDelMes[fecha].length > 0) {
        const nota = notasDelMes[fecha][0];
        document.getElementById('tituloModal').textContent = 'Editar Nota';
        document.getElementById('notaId').value = nota.id;
        document.getElementById('notaTexto').value = nota.note;
        document.getElementById('notaPrioridad').value = nota.priority;
        document.getElementById('btnEliminar').classList.remove('hidden');
    } else {
        document.getElementById('tituloModal').textContent = 'Agregar Nota';
    }
    
    document.getElementById('modalNota').classList.remove('hidden');
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modalNota').classList.add('hidden');
}

// Guardar nota (FUNCIONA PARA CUALQUIER DÍA)
// Guardar nota (CON VALIDACIÓN Y DEBUG MEJORADO)
function guardarNota() {
    const notaId = document.getElementById('notaId').value;
    const fecha = document.getElementById('notaFecha').value;
    const texto = document.getElementById('notaTexto').value.trim();
    const prioridad = document.getElementById('notaPrioridad').value;
    
    console.log('=== INTENTANDO GUARDAR NOTA ===');
    console.log('ID:', notaId || 'Nueva nota');
    console.log('Fecha:', fecha);
    console.log('Texto:', texto);
    console.log('Prioridad:', prioridad);
    
    if (!texto) {
        alert('Por favor escribe una nota');
        return;
    }
    
    if (!fecha) {
        alert('Error: No hay fecha seleccionada');
        console.error('No hay fecha!');
        return;
    }
    
    const datos = {
        date: fecha,
        note: texto,
        priority: prioridad
    };
    
    console.log('Datos a enviar:', JSON.stringify(datos, null, 2));
    
    const url = notaId ? `/calendar/notes/${notaId}` : '/calendar/notes';
    const metodo = notaId ? 'PUT' : 'POST';
    
    console.log('URL:', url);
    console.log('Método:', metodo);
    
    // Obtener token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    console.log('Token CSRF encontrado:', csrfToken ? 'SÍ' : 'NO');
    
    if (!csrfToken) {
        console.error('¡ERROR! No se encontró el token CSRF');
        alert('Error de configuración: Falta token CSRF. Recarga la página.');
        return;
    }
    
    fetch(url, {
        method: metodo,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken.content
        },
        body: JSON.stringify(datos)
    })
    .then(res => {
        console.log('Respuesta recibida. Status:', res.status);
        console.log('Headers:', res.headers);
        
        if (!res.ok) {
            return res.text().then(text => {
                console.error('Respuesta del servidor (ERROR):', text);
                throw new Error(`Error ${res.status}: ${text.substring(0, 200)}`);
            });
        }
        
        return res.json();
    })
    .then(data => {
        console.log('Respuesta JSON:', data);
        
        if (data.success) {
            console.log('✅ Nota guardada exitosamente!');
            cerrarModal();
            cargarCalendario();
        } else {
            console.error('❌ El servidor respondió con error:', data);
            alert('Error al guardar: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(err => {
        console.error('❌ Error capturado:', err);
        console.error('Stack:', err.stack);
        alert('Error al guardar la nota: ' + err.message);
    });
}

// Eliminar nota
function eliminarNota() {
    if (!confirm('¿Seguro que quieres eliminar esta nota?')) return;
    
    const notaId = document.getElementById('notaId').value;
    
    fetch(`/calendar/notes/${notaId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cerrarModal();
            cargarCalendario();
        } else {
            alert('Error al eliminar la nota');
        }
    })
    .catch(err => {
        console.error('Error eliminando nota:', err);
        alert('Error al eliminar la nota');
    });
}

// Formatear fecha para mostrar (EN ESPAÑOL)
function formatearFecha(fecha) {
    const partes = fecha.split('-');
    const año = parseInt(partes[0]);
    const mes = parseInt(partes[1]) - 1;
    const dia = parseInt(partes[2]);
    
    const f = new Date(año, mes, dia);
    const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    return `${diasSemana[f.getDay()]}, ${dia} de ${meses[mes]} de ${año}`;
}
</script>

@endsection
