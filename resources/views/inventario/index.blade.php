<x-app-layout>
    <div x-data="{ openImage:false, imgSrc:'' }"
         @open-image.window="imgSrc = $event.detail; openImage = true">

        <x-slot name="header">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800">📦 Inventario</h2>
                <a href="{{ route('productos.create') }}"
                   class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                    Nuevo
                </a>
            </div>
        </x-slot>

        <div class="max-w-7xl mx-auto p-4">
            {{-- Buscador en tiempo real --}}
            <div class="mb-3">
                <div class="flex gap-2">
                    <input id="buscador" type="text" placeholder="Buscar producto, código o proveedor…"
                           class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>



@if(!empty($totals))
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
  <div class="rounded-xl bg-white p-4 shadow">
    <div class="text-sm text-gray-500">Productos (filtrados)</div>
    <div class="text-2xl font-semibold">{{ number_format($totals['items']) }}</div>
  </div>
  <div class="rounded-xl bg-white p-4 shadow">
    <div class="text-sm text-gray-500">Piezas en stock</div>
    <div class="text-2xl font-semibold">{{ number_format($totals['qty']) }}</div>
  </div>
  <div class="rounded-xl bg-white p-4 shadow">
    <div class="text-sm text-gray-500">Valor a costo</div>
    <div class="text-2xl font-semibold">L {{ number_format($totals['cost_value'],2) }}</div>
  </div>
  <div class="rounded-xl bg-white p-4 shadow">
    <div class="text-sm text-gray-500">Valor a venta</div>
    <div class="text-xl font-semibold">L {{ number_format($totals['retail_value'],2) }}</div>
    <div class="text-xs text-gray-500">Margen potencial: L {{ number_format($totals['potential_margin'],2) }}</div>
  </div>
</div>
@endif



            {{-- Zona dinámica --}}
            <div id="zona-lista">
                @include('inventario.partials.tabla', ['products' => $products])
            </div>
        </div>

        {{-- Modal de imagen ampliada --}}
        <div x-show="openImage"
             x-transition.opacity
             class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4"
             @keydown.escape.window="openImage=false"
             @click.self="openImage=false"
             style="display:none">
          <img :src="imgSrc"
               class="max-h-[85vh] max-w-[90vw] rounded-xl shadow-2xl bg-white"
               alt="Imagen del producto">
        </div>
    </div>

    {{-- Script directo (no depende de @push/@stack) --}}
    <script>
    (function(){
      const input = document.getElementById('buscador');
      const zona  = document.getElementById('zona-lista');
      if(!input || !zona) return;

      let timer = null;
      const debounce = (fn, ms=400) => { clearTimeout(timer); timer = setTimeout(fn, ms); };

      async function cargar(url){
        zona.classList.add('opacity-60');
        try{
          const resp = await fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest'} });
          zona.innerHTML = await resp.text();

          // Paginación AJAX
          zona.querySelectorAll('.pagination a, nav[role="navigation"] a').forEach(a=>{
            a.addEventListener('click', e=>{
              e.preventDefault();
              cargar(a.getAttribute('href'));
            });
          });
        }catch(e){ console.error(e); }
        zona.classList.remove('opacity-60');
      }

      function buscar(q){
        const base = `{{ route('inventario.index') }}`;
        const url  = q ? `${base}?q=${encodeURIComponent(q)}` : base;
        cargar(url);
      }

      input.addEventListener('input', e => debounce(()=>buscar(e.target.value)));
    })();
    </script>
</x-app-layout>
