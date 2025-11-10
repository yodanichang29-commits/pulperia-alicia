@props(['size' => 'w-36 h-36']) {{-- tamaño por defecto, pero se puede sobreescribir --}}

<img
  src="{{ asset('images/logo-alicia.png') }}"
  alt="Pulpería Alicia"
  {{ $attributes->merge(['class' => "inline-block object-contain $size"]) }}
/>
