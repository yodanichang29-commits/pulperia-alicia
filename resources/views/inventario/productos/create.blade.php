<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Nuevo producto</h2></x-slot>
  <div class="max-w-3xl mx-auto p-4">
    <form action="{{ route('productos.store') }}" 
      method="POST" 
      enctype="multipart/form-data"   {{-- 👈 importante --}}
      class="bg-white p-4 rounded-xl shadow">
    @csrf
    @include('inventario.productos._form', ['btn' => 'Crear'])
</form>

  </div>
</x-app-layout>
