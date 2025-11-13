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

    {{-- SECCIÓN DE PAGOS (solo para compras) --}}
    @if($transaction->type === 'in' && $transaction->reason === 'purchase')
      @php
        $payments = $transaction->payments;
        $totalPagado = $payments->sum('amount');
        $saldoPendiente = $transaction->total_cost - $totalPagado;

        // Estado de pago
        if ($saldoPendiente <= 0) {
            $estadoColor = 'emerald';
            $estadoTexto = 'PAGADA COMPLETAMENTE';
            $estadoIcon = '✅';
        } elseif ($totalPagado > 0) {
            $estadoColor = 'amber';
            $estadoTexto = 'PAGO PARCIAL';
            $estadoIcon = '⚠️';
        } else {
            $estadoColor = 'red';
            $estadoTexto = 'PENDIENTE DE PAGO';
            $estadoIcon = '❌';
        }
      @endphp

      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-lg p-6 border border-blue-200">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Estado de Pagos
          </h3>

          {{-- Badge de estado --}}
          <span class="px-4 py-2 rounded-full text-sm font-bold bg-{{ $estadoColor }}-100 text-{{ $estadoColor }}-700 border-2 border-{{ $estadoColor }}-300">
            {{ $estadoIcon }} {{ $estadoTexto }}
          </span>
        </div>

        {{-- Resumen financiero --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
          <div class="bg-white rounded-lg p-4 shadow">
            <div class="text-xs font-medium text-gray-500 uppercase mb-1">Total Compra</div>
            <div class="text-2xl font-bold text-gray-900">L {{ number_format($transaction->total_cost, 2) }}</div>
          </div>

          <div class="bg-white rounded-lg p-4 shadow">
            <div class="text-xs font-medium text-gray-500 uppercase mb-1">Total Pagado</div>
            <div class="text-2xl font-bold text-emerald-600">L {{ number_format($totalPagado, 2) }}</div>
          </div>

          <div class="bg-white rounded-lg p-4 shadow">
            <div class="text-xs font-medium text-gray-500 uppercase mb-1">Saldo Pendiente</div>
            <div class="text-2xl font-bold text-{{ $saldoPendiente > 0 ? 'amber' : 'emerald' }}-600">
              L {{ number_format($saldoPendiente, 2) }}
            </div>
          </div>
        </div>

        {{-- Lista de pagos --}}
        <div class="bg-white rounded-lg p-4">
          <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Historial de Pagos
          </h4>

          @forelse($payments as $payment)
            <div class="flex items-center justify-between p-3 mb-2 border-l-4 @if($payment->affects_cash) border-red-500 bg-red-50 @else border-blue-500 bg-blue-50 @endif rounded-r-lg">
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  {{-- Icono según método --}}
                  <div class="flex-shrink-0">
                    @switch($payment->payment_method)
                      @case('caja')
                        <span class="text-2xl">💵</span>
                        @break
                      @case('efectivo_personal')
                        <span class="text-2xl">💰</span>
                        @break
                      @case('credito')
                        <span class="text-2xl">📋</span>
                        @break
                      @case('transferencia')
                        <span class="text-2xl">🏦</span>
                        @break
                      @case('tarjeta')
                        <span class="text-2xl">💳</span>
                        @break
                    @endswitch
                  </div>

                  <div>
                    <div class="font-semibold text-gray-900">
                      {{ $payment->payment_method_label }}
                    </div>
                    <div class="text-xs text-gray-600">
                      {{ $payment->created_at->format('d/m/Y h:i A') }}
                      · Por {{ $payment->user->name }}
                      @if($payment->affects_cash)
                        <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-medium">
                          Afectó caja
                        </span>
                      @endif
                    </div>
                    @if($payment->notes)
                      <div class="text-xs text-gray-500 mt-1">
                        <span class="font-medium">Nota:</span> {{ $payment->notes }}
                      </div>
                    @endif
                  </div>
                </div>
              </div>

              <div class="text-right ml-4">
                <div class="text-xl font-bold text-gray-900">
                  L {{ number_format($payment->amount, 2) }}
                </div>
              </div>
            </div>
          @empty
            <div class="text-center py-8 text-gray-500">
              <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm font-medium">No hay pagos registrados</p>
              <p class="text-xs mt-1">Esta compra está pendiente de pago</p>
            </div>
          @endforelse

          {{-- Línea totalizadora --}}
          @if($payments->count() > 0)
            <div class="mt-4 pt-4 border-t-2 border-gray-300">
              <div class="flex justify-between items-center">
                <span class="text-sm font-semibold text-gray-700 uppercase">Total Pagado:</span>
                <span class="text-2xl font-bold text-emerald-600">L {{ number_format($totalPagado, 2) }}</span>
              </div>

              @if($saldoPendiente > 0)
                <div class="flex justify-between items-center mt-2">
                  <span class="text-sm font-semibold text-gray-700 uppercase">Saldo Pendiente:</span>
                  <span class="text-2xl font-bold text-amber-600">L {{ number_format($saldoPendiente, 2) }}</span>
                </div>
              @endif
            </div>
          @endif
        </div>

        {{-- Botón para agregar abono (preparado para futura implementación) --}}
        @if($saldoPendiente > 0 && !$transaction->voided_at)
          <div class="mt-4 flex justify-end">
            <button
              onclick="alert('Funcionalidad de abonos próximamente disponible')"
              class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Agregar Abono
            </button>
          </div>
        @endif
      </div>
    @endif

  </div>
</x-app-layout>


