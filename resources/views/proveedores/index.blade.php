<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800">📦 Proveedores</h2>
      <a href="{{ route('proveedores.create') }}"
         class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
        Nuevo
      </a>
    </div>
  </x-slot>

  <div class="max-w-7xl mx-auto p-4">
    {{-- Alertas flash --}}
    @if (session('ok'))
      <div class="mb-3 rounded-lg bg-green-50 text-green-800 px-4 py-2">{{ session('ok') }}</div>
    @endif

    {{-- Buscador en tiempo real --}}
    <div class="mb-3">
      <div class="flex gap-2">
        <input id="buscador" type="text" placeholder="Buscar proveedor, contacto, teléfono, correo…"
               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
    </div>

    {{-- Zona dinámica --}}
    <div id="zona-lista">
      @include('proveedores.partials.tabla', ['providers' => $providers])
    </div>
  </div>

  {{-- ⚠️ Script directo (no usa @push) --}}
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

        // Reengancha paginación para AJAX
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
      const base = `{{ route('proveedores.index') }}`;
      const url  = q ? `${base}?q=${encodeURIComponent(q)}` : base;
      cargar(url);
    }

    input.addEventListener('input', e => debounce(()=>buscar(e.target.value)));
  })();
  </script>
</x-app-layout>
