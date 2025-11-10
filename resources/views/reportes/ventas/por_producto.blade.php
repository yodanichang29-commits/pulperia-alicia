{{-- resources/views/reportes/ventas/por_producto.blade.php --}}
@php
    $g = request('group', 'venta'); // valor por defecto: 'venta'
    // Sanitiza por si te mandan algo raro:
    $valid = ['venta','dia','precio','hora','lineas'];
    if (!in_array($g, $valid, true)) { $g = 'venta'; }
@endphp

<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">
      Ventas por producto — <span class="text-blue-700">Pulpería Alicia</span>
    </h2>
      @include('reportes.ventas.partials.nav')
  </x-slot>

  {{-- Sub-nav de Reporte Ventas --}}


  {{-- Filtros --}}
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 md:mt-8">
    <form method="GET" action="{{ route('reportes.ventas.producto') }}"
          class="bg-white rounded-xl p-4 shadow mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
        <input type="date" name="start" value="{{ $start }}"
               class="w-full rounded-lg border-gray-300">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
        <input type="date" name="end" value="{{ $end }}"
               class="w-full rounded-lg border-gray-300">
      </div>

      <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
        {{-- input visible para buscar --}}
        <input id="product_search" type="text" placeholder="Escribe nombre o código…"
               class="w-full rounded-lg border-gray-300" autocomplete="off">
        {{-- ID real que se envía --}}
        <input type="hidden" name="product_id" id="product_id" value="{{ $productId }}">

        {{-- dropdown de sugerencias --}}
        <div id="suggestions"
             class="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow hidden"></div>
      </div>

      <div class="md:col-span-3 flex items-center gap-3">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          FILTRAR
        </button>

        <a href="{{ route('reportes.ventas.producto') }}"
           class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">
          Limpiar
        </a>
      </div>
    </form>

    {{-- Mensaje cuando no hay producto seleccionado --}}
    @if(!$productId)
      <p class="text-gray-500">Selecciona un producto para ver resultados.</p>
    @endif

    {{-- Resultados --}}
    @if($productId && $producto)
      {{-- Resumen --}}
      <div class="bg-white rounded-xl shadow p-4 mb-6">
        <h3 class="text-lg font-semibold mb-3">{{ $producto->name }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500">Unidades vendidas</div>
            <div class="text-2xl font-bold">{{ number_format($resumen->qty ?? 0, 2) }}</div>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500">Total L</div>
            <div class="text-2xl font-bold">L {{ number_format($resumen->total ?? 0, 2) }}</div>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500">Precio promedio</div>
            @php
              $pp = ($resumen->qty ?? 0) > 0 ? ($resumen->total / $resumen->qty) : 0;
            @endphp
            <div class="text-2xl font-bold">L {{ number_format($pp, 2) }}</div>
          </div>
        </div>
      </div>



<div class="flex gap-2 mb-3">
  @php $g = $g ?? 'lineas'; @endphp
  <a href="{{ request()->fullUrlWithQuery(['group'=>'lineas']) }}"  class="{{ $g==='lineas' ? 'bg-blue-600 text-white px-3 py-1 rounded' : 'bg-gray-100 px-3 py-1 rounded' }}">Por venta</a>
  <a href="{{ request()->fullUrlWithQuery(['group'=>'dia']) }}"     class="{{ $g==='dia' ? 'bg-blue-600 text-white px-3 py-1 rounded' : 'bg-gray-100 px-3 py-1 rounded' }}">Por día</a>
  <a href="{{ request()->fullUrlWithQuery(['group'=>'precio']) }}"  class="{{ $g==='precio' ? 'bg-blue-600 text-white px-3 py-1 rounded' : 'bg-gray-100 px-3 py-1 rounded' }}">Por precio</a>
<a href="{{ request()->fullUrlWithQuery(['group'=>'hora']) }}"
   class="{{ $group==='hora' ? 'bg-blue-600 text-white px-3 py-1 rounded' : 'text-blue-600 px-3 py-1' }}">
  Por hora
</a>
</div>


@if($g === 'lineas')
  {{-- tabla de líneas --}}
@endif

@if($g === 'dia')
  {{-- tabla por día --}}
@endif

@if($g === 'precio')
  {{-- tabla por precio --}}
@endif

@if($g === 'hora')
  {{-- tabla por hora --}}
@endif







      {{-- Tabla por día --}}
      <div class="bg-white rounded-xl shadow overflow-x-auto">
       <table class="min-w-full">
  <thead>
    <tr>
      <th class="px-4 py-2 text-left">Hora</th>
      <th class="px-4 py-2 text-right">Unidades</th>
      <th class="px-4 py-2 text-right">Total (L)</th>
      <th class="px-4 py-2 text-right">Precio prom (L)</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($porHora as $row)
      <tr>
        <td class="px-4 py-2">{{ $row->hora_bloque }}</td>
        <td class="px-4 py-2 text-right">{{ number_format($row->qty, 2) }}</td>
        <td class="px-4 py-2 text-right">L {{ number_format($row->total, 2) }}</td>
        <td class="px-4 py-2 text-right">L {{ number_format($row->precio_prom, 2) }}</td>
      </tr>
    @empty
      <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin ventas en el rango</td></tr>
    @endforelse
  </tbody>
</table>
      </div>
    @endif
  </div>

  {{-- Autocomplete (vanilla JS) --}}
  <script>
    const $search = document.getElementById('product_search');
    const $list   = document.getElementById('suggestions');
    const $id     = document.getElementById('product_id');

    let timer = null;

    function hideList(){ $list.classList.add('hidden'); $list.innerHTML=''; }

    $search.addEventListener('input', () => {
      const q = $search.value.trim();
      $id.value = ''; // invalidar selección si cambia el texto
      if(!q){ hideList(); return; }

      clearTimeout(timer);
      timer = setTimeout(async () => {
        const url = "{{ route('reportes.ventas.buscar_productos') }}?q=" + encodeURIComponent(q);
        const res = await fetch(url);
        const items = await res.json();

        if(!items.length){ hideList(); return; }

        $list.innerHTML = items.map(i =>
          `<button type="button" data-id="${i.id}"
                   class="w-full text-left px-3 py-2 hover:bg-gray-100">${i.label}</button>`
        ).join('');
        $list.classList.remove('hidden');

        // click item
        Array.from($list.children).forEach(btn => {
          btn.addEventListener('click', () => {
            $id.value = btn.dataset.id;
            $search.value = btn.innerText;
            hideList();
          });
        });
      }, 250);
    });

    // cerrar lista si haces click fuera
    document.addEventListener('click', (e) => {
      if(! $list.contains(e.target) && e.target !== $search){ hideList(); }
    });
  </script>
</x-app-layout>
