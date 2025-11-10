@props(['title'=>''])
<div class="bg-white rounded-2xl shadow p-4">
  <div class="font-semibold mb-2">{{ $title }}</div>
  <ul class="space-y-1">
    {{ $slot }}
  </ul>
</div>
