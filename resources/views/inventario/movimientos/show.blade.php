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

    {{-- Sección de información de pagos (solo para compras) --}}
    @if($transaction->type === 'in' && $transaction->reason === 'purchase')
      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-lg p-5 border border-indigo-200">
        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-2 text-indigo-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
          </svg>
          Información de Pago
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          {{-- Pago desde caja --}}
          <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="text-xs text-gray-500 mb-1">💵 Pago desde caja</div>
            <div class="text-lg font-bold text-gray-900">
              L {{ number_format($transaction->paid_from_cash, 2) }}
            </div>
            @if($transaction->paid_from_cash > 0)
              <div class="text-xs text-gray-600 mt-1">Descontado del turno</div>
            @endif
          </div>

          {{-- Pago desde fondos externos --}}
          <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="text-xs text-gray-500 mb-1">🏦 Pago externo</div>
            <div class="text-lg font-bold text-gray-900">
              L {{ number_format($transaction->paid_from_outside, 2) }}
            </div>
            @if($transaction->paid_from_outside > 0)
              <div class="text-xs text-gray-600 mt-1">Banco/Dueño/Otros</div>
            @endif
          </div>

          {{-- Saldo pendiente --}}
          <div class="bg-white rounded-lg p-4 border-2
                      @if($transaction->pending_balance > 0) border-amber-400 @else border-green-400 @endif">
            <div class="text-xs text-gray-500 mb-1">📋 Saldo pendiente</div>
            <div class="text-lg font-bold
                        @if($transaction->pending_balance > 0) text-amber-600 @else text-green-600 @endif">
              L {{ number_format($transaction->pending_balance, 2) }}
            </div>
            @if($transaction->is_fully_paid)
              <div class="text-xs text-green-600 mt-1 font-medium">✓ Completamente pagada</div>
            @else
              <div class="text-xs text-amber-600 mt-1">Pendiente de pago</div>
            @endif
          </div>
        </div>

        {{-- Resumen total --}}
        <div class="mt-4 pt-4 border-t border-indigo-200">
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Total de la compra:</span>
            <span class="font-semibold text-gray-900">L {{ number_format($transaction->total_cost, 2) }}</span>
          </div>
          <div class="flex justify-between items-center text-sm mt-1">
            <span class="text-gray-600">Total pagado:</span>
            <span class="font-semibold text-gray-900">L {{ number_format($transaction->total_paid, 2) }}</span>
          </div>
        </div>
      </div>
    @endif

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


