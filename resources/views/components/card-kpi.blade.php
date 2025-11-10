@props(['title'=>'','today'=>0,'yesterday'=>0,'pct'=>null,'arrow'=>'=','color'=>'text-gray-500'])
<div class="bg-white rounded-2xl shadow p-4">
  <div class="text-sm text-gray-500">{{ $title }}</div>
  <div class="flex items-end gap-3 mt-1">
    <div class="text-4xl font-extrabold">{{ $today }}</div>
    <div class="text-sm text-gray-500">ayer {{ $yesterday }}</div>
  </div>
  <div class="mt-2 text-xl font-semibold {{ $color }}">
    @if(!is_null($pct))
      {{ $arrow }} {{ $pct }}%
    @else
      —
    @endif
  </div>
</div>
