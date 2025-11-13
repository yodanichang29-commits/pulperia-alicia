<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800">
        Movimiento #{{ $transaction->id }}
      </h2>

{{-- Alerta de movimiento anulado --}}
@if($transaction->voided_at)
  <div class="flex items-start gap-3 bg-rose-50 border border-rose-200 rounded-xl p-3 mb-4 text-sm text-rose-800 shadow-sm">
    <div class="flex-shrink-0 mt-0.5">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M4.93 4.93a10 10 0 0114.14 0m0 14.14a10 10 0 01-14.14 0" />
      </svg>
    </div>
    <div>
      <p class="font-semibold">Este movimiento fue ANULADO.</p>
      <p>
        <span class="text-gray-600">Por:</span>
        {{ $transaction->voider?->name ?? 'Usuario desconocido' }}
        <span class="mx-2 text-gray-400">•</span>
        <span class="text-gray-600">Fecha:</span>
        {{ \Carbon\Carbon::parse($transaction->voided_at)->format('d/m/Y h:i A') }}
      </p>
      <p class="mt-1 text-rose-700">El stock fue revertido automáticamente.</p>
    </div>
  </div>
@endif



{{-- Botón anular (solo si NO está anulado) --}}
<div class="flex items-center justify-between">
 
  @if($transaction->voided_at)
    <span class="px-3 py-1 rounded-lg bg-rose-100 text-rose-700 text-sm font-semibold">
      ANULADO
    </span>
  @else
    <form method="POST" action="{{ route('ingresos.void', $transaction) }}"
          onsubmit="return confirm('¿Seguro que deseas anular este movimiento? Esto revertirá el stock.');">
      @csrf
      <button type="submit"
              class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg">
        Anular
      </button>
    </form>
  @endif
</div>










      <a href="{{ route('ingresos.index') }}"
         class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Regresar
      </a>
    </div>
  </x-slot>



  <div class="max-w-5xl mx-auto p-4 space-y-4">
    <div class="bg-white rounded-xl shadow p-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
        <div><span class="text-gray-500">Fecha:</span> {{ optional($transaction->moved_at)->format('d/m/Y') }}</div>
        <div><span class="text-gray-500">Tipo:</span> {{ $transaction->type === 'in' ? 'Entrada' : 'Salida' }}</div>
        <div><span>Motivo:</span> {{ $transaction->reason_label }}</div>

        <div><span class="text-gray-500">Usuario:</span> {{ $transaction->user?->name }}</div>
        <div class="md:col-span-2"><span>Proveedor: {{ $transaction->provider?->name ?? '—' }}</span></div>
        <div><span class="text-gray-500">Ref.:</span> {{ $transaction->reference ?? '—' }}</div>
        <div class="font-semibold text-right">Total: L {{ number_format($transaction->total_cost,2) }}</div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left">Producto</th>
            <th class="px-3 py-2 text-right">Antes</th>
            <th class="px-3 py-2 text-right">Cant.</th>
            <th class="px-3 py-2 text-right">Después</th>
            <th class="px-3 py-2 text-right">Costo unit.</th>
            <th class="px-3 py-2 text-right">Total</th>
          </tr>
        </thead>
     <tbody>
@forelse($transaction->items as $it)
  <tr class="border-t">
    <td class="px-4 py-2">
      {{ $it->product?->name ?? 'Producto eliminado' }}
    </td>

    {{-- Si aún no guardamos "antes/después", los dejamos en blanco o N/D --}}
   <td class="px-4 py-2 text-right">{{ $it->before_qty }}</td>
<td class="px-4 py-2 text-right">{{ $it->qty }}</td>
<td class="px-4 py-2 text-right">{{ $it->after_qty }}</td>


    <td class="px-4 py-2 text-right">
      L {{ number_format($it->unit_cost, 2) }}
    </td>

    <td class="px-4 py-2 text-right">
      L {{ number_format($it->qty * $it->unit_cost, 2) }}
    </td>
  </tr>
@empty
  <tr>
    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
      Sin productos en este movimiento.
    </td>
  </tr>
@endforelse
</tbody>

      </table>
    </div>
  </div>
</x-app-layout>


