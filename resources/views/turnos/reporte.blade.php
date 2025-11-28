<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 Reporte de Turno #{{ $shift->id }}
            </h2>
            <div class="flex gap-2">
                <button onclick="window.print()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    🖨️ Imprimir
                </button>
                <a href="/caja" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    ← Volver al POS
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            
            {{-- Información del Turno --}}
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">📋 Información del Turno</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600">Fecha</div>
                        <div class="text-lg font-semibold">{{ $shift->opened_at->format('d/m/Y') }}</div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600">Horario</div>
                        <div class="text-lg font-semibold">
                            {{ $shift->opened_at->format('H:i') }} - {{ $shift->closed_at->format('H:i') }}
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600">Cajero</div>
                        <div class="text-lg font-semibold">{{ $shift->user->name }}</div>
                    </div>
                </div>
            </div>

            {{-- Resumen de Caja --}}
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">💰 Resumen de Caja</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-700">Fondo inicial:</span>
                            <span class="font-semibold">L {{ number_format($shift->opening_float, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-700">Efectivo esperado:</span>
                            <span class="font-semibold">L {{ number_format($shift->expected_cash, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-700">Efectivo contado:</span>
                            <span class="font-semibold">L {{ number_format($shift->closing_cash_count, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-t-2 border-gray-300">
                            <span class="font-bold text-gray-900">Diferencia:</span>
                            <span class="font-bold text-lg {{ $shift->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                L {{ number_format($shift->difference, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-semibold text-gray-800 mb-3">Ventas por Método</h4>
                        @foreach($summary['by_payment'] as $method => $data)
                            <div class="flex justify-between py-2 border-b">
                                <span class="text-gray-700 uppercase">{{ $method }}:</span>
                                <span class="font-semibold">L {{ number_format($data['total'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Ventas por Categoría --}}
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">📦 Ventas por Categoría</h3>
                
                @if($salesByCategory->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        No se registraron ventas en este turno
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($salesByCategory as $category)
                            <div class="border-2 border-gray-200 rounded-xl p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="text-xl font-bold text-gray-900">
                                        {{ $category['name'] }}
                                    </h4>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-600">Total vendido</div>
                                        <div class="text-2xl font-bold text-blue-600">
                                            L {{ number_format($category['total'], 2) }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Productos de la categoría --}}
                                <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                    <div class="text-sm font-semibold text-gray-700 mb-2">Productos vendidos:</div>
                                    @foreach($category['products'] as $product)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-0">
                                            <div class="flex-1">
                                                <span class="font-medium text-gray-800">{{ $product['name'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <span class="text-sm text-gray-600">
                                                    Cant: <span class="font-semibold">{{ number_format($product['qty'], 2) }}</span>
                                                </span>
                                                <span class="font-semibold text-gray-900">
                                                    L {{ number_format($product['total'], 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total General --}}
                    <div class="mt-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
                        <div class="flex justify-between items-center">
                            <h3 class="text-2xl font-bold">Total General de Ventas</h3>
                            <div class="text-4xl font-black">
                                L {{ number_format($salesByCategory->sum('total'), 2) }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
    @endpush
</x-app-layout>