@props(['titulo','items'])

<div class="rounded-xl bg-white shadow">
  <div class="px-4 py-3 border-b font-semibold">{{ $titulo }}</div>

  <div class="max-h-64 overflow-auto divide-y">
    @forelse ($items as $it)
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
