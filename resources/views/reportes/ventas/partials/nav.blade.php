@php
$tabs = [
  ['label' => '📊 General',            'route' => 'reportes.ventas.index'],
  ['label' => '🏷️ Por proveedor',      'route' => 'reportes.ventas.proveedores'],
  ['label' => '🧾 Ventas por producto', 'route' => 'reportes.ventas.producto'],
  ['label' => '🧍 Detalle',             'route' => 'reportes.ventas.detalle'], // si aplica
  ['label' => '🕐 Turnos',             'route' => 'reportes.ventas.turnos'],
];

$sticky = $sticky ?? true; // <- permite desactivar sticky desde el include
@endphp

<nav class="{{ $sticky ? 'sticky top-0 z-20 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60 border-b' : '' }}">
  <div class="mx-auto max-w-7xl">
    <div class="overflow-x-auto scrollbar-none">
      <ul class="flex gap-1 p-1 my-3 rounded-2xl bg-gray-100 w-max">
        @foreach ($tabs as $t)
          @php $active = request()->routeIs($t['route']); @endphp
          <li>
            <a href="{{ route($t['route']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-medium transition
                      {{ $active ? 'bg-white text-blue-700 shadow-sm ring-1 ring-black/5'
                                 : 'text-gray-600 hover:text-gray-900 hover:bg-white/70' }}">
              <span>{{ $t['label'] }}</span>
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</nav>
