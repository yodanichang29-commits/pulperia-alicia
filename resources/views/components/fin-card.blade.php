@props([
  'title' => '',
  'value' => 0,         // número o texto
  'hint'  => null,      // texto chico debajo
  'negative' => false,  // true = rojo, false = verde
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border shadow-sm p-4']) }}>
  <div class="text-sm text-gray-500">{{ $title }}</div>

  <div class="mt-1 text-2xl font-semibold {{ $negative ? 'text-red-600' : 'text-emerald-600' }}">
    @if (is_numeric($value))
      L {{ number_format((float)$value, 2) }}
    @else
      {{ $value }}
    @endif
  </div>

  @if ($hint)
    <div class="mt-1 text-xs text-gray-400">{{ $hint }}</div>
  @endif
</div>
