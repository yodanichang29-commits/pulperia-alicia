
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">Reporte general de ventas — Pulpería Alicia</h2>
 
     @include('reportes.ventas.partials.nav')

  </x-slot>

  <div class="max-w-7xl mx-auto p-4">

    {{-- Filtros (fechas + buscador en tiempo real) --}}
    <form id="filtros" class="bg-white p-4 rounded-xl shadow flex flex-col sm:flex-row gap-3 mb-4">
      <div>
        <label class="block text-sm text-gray-700">Desde</label>
        <input type="date" id="desde" value="{{ $desde }}"
               class="mt-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm text-gray-700">Hasta</label>
        <input type="date" id="hasta" value="{{ $hasta }}"
               class="mt-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div class="flex-1">
        <label class="block text-sm text-gray-700">Buscar</label>
        <input type="text" id="q" value="{{ $q }}" placeholder="Proveedor o producto…"
               class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
    </form>

    {{-- Zona dinámica --}}
  <div id="zona-lista">
  @include('reportes.ventas.partials.proveedores_contenido', ['rows'=>$rows,'total_general'=>$total_general,'detalle'=>$detalle])
</div>

  </div>

  <script>
  (function(){
    const zona  = document.getElementById('zona-lista');
    const desde = document.getElementById('desde');
    const hasta = document.getElementById('hasta');
    const q     = document.getElementById('q');

    let t=null; const deb=(f,m=400)=>{clearTimeout(t); t=setTimeout(f,m)};

    async function cargar(){
      const url = new URL(`{{ route('reportes.ventas.proveedores') }}`, window.location.origin);
      if(desde.value) url.searchParams.set('desde', desde.value);
      if(hasta.value) url.searchParams.set('hasta', hasta.value);
      if(q.value)     url.searchParams.set('q', q.value);

      zona.classList.add('opacity-60');
      const resp = await fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest'} });
      zona.innerHTML = await resp.text();
      zona.classList.remove('opacity-60');
    }

    [desde,hasta].forEach(el => el.addEventListener('change', cargar));
    q.addEventListener('input', ()=>deb(cargar));
  })();
  </script>
</x-app-layout>
