@props(['value' => null])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700']) }}>
    @if($value)
        {{ $value }}
    @else
        {{ $slot }}
    @endif
</label>
