<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">➕ Nuevo proveedor</h2>
  </x-slot>

  <div class="max-w-4xl mx-auto p-4">
    <form action="{{ route('proveedores.store') }}" method="POST" class="bg-white p-4 rounded-xl shadow">
      @include('proveedores._form', ['btn' => 'Crear'])
    </form>
  </div>
</x-app-layout>
