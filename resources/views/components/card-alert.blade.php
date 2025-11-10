@props([
  'title' => '',
  'value' => 0,
  'subtitle' => '',
  'color' => 'gray',
  'link' => null,   // <- recibimos el link como prop
])

@php
  $colors = [
    'red' => 'border-red-200',
    'amber' => 'border-amber-200',
    'yellow' => 'border-yellow-200',
    'green' => 'border-green-200',
    'blue' => 'border-blue-200',
    'gray' => 'border-gray-200',
  ];
  $border = $colors[$color] ?? $colors['gray'];
@endphp

<div class="bg-white rounded-2xl shadow p-4 border {{ $border }}">
  <div class="text-sm text-gray-600">{{ $title }}</div>
  <div class="text-4xl font-semibold text-gray-900 mt-1">{{ $value }}</div>
  <div class="text-sm text-gray-500 mt-1">{{ $subtitle }}</div>

  @if($link)
    <div class="mt-2">
      <a href="{{ $link }}"
         class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center">
        Ver
        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5l7 7-7 7" />
        </svg>
      </a>
    </div>
  @endif
</div>
