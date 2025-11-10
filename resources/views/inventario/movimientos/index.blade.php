<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">Movimientos de inventario</h2>
  </x-slot>

  <div class="max-w-6xl mx-auto p-4">
    <div class="mb-4">
<a href="{{ route('ingresos.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Nuevo</a>
    </div>


<form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
  <div>
    <label class="block text-xs text-gray-500">Desde</label>
    <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-gray-300">
  </div>

  <div>
    <label class="block text-xs text-gray-500">Hasta</label>
    <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-gray-300">
  </div>

  <div>
    <label class="block text-xs text-gray-500">Tipo</label>
    <select name="type" class="rounded-lg border-gray-300">
      <option value="">Todos</option>
      <option value="in"  @selected($type==='in')>Entradas</option>
      <option value="out" @selected($type==='out')>Salidas</option>
    </select>
  </div>



<div>
  <label class="block text-xs text-gray-500">Proveedor</label>
  <select name="provider_id" class="rounded-lg border-gray-300">
    <option value="">Todos</option>
    @foreach($providers as $id => $name)
      <option value="{{ $id }}" @selected(($providerId ?? '') == $id)>
        {{ $name }}
      </option>
    @endforeach
  </select>
</div>

<button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Filtrar</button>
<a href="{{ route('ingresos.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg">Limpiar</a>

</form>



    <div class="bg-white rounded-xl shadow">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left">Fecha</th>
            <th class="px-4 py-2 text-left">Tipo</th>
            <th class="px-4 py-2 text-left">Motivo</th>
            <th class="px-4 py-2 text-left">Hecho por</th>
            <th class="px-4 py-2 text-right">Total (L)</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody>
@foreach ($txs as $t)
          <tr class="border-t">
            <td class="px-4 py-2">{{ optional($t->moved_at)->format('d/m/Y') }}</td>
            <td class="px-4 py-2">
              @if($t->type === 'in') <span class="text-green-700">Entrada</span>
              @else <span class="text-rose-700">Salida</span> @endif
            </td>
            <td class="px-4 py-2">{{ $t->reason_label }}</td>
            <td class="px-4 py-2">{{ $t->user?->name }}</td>
           <td class="px-4 py-2 text-right">
  @if($t->voided_at)
    <span class="px-2 py-1 text-xs rounded bg-rose-100 text-rose-700">ANULADO</span>
  @else
    L {{ number_format($t->total_cost, 2) }}
  @endif
</td>
<td class="px-4 py-2 text-right">
  <a href="{{ route('ingresos.show', $t) }}" class="text-indigo-600 underline">Ver</a>
</td>

            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

<div class="mt-4">{{ $txs->links() }}</div>
  </div>
</x-app-layout>
