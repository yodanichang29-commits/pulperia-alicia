<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📊 Resumen por categoría — Turno #{{ $shift->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-2xl p-6 space-y-5">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <p class="text-sm text-gray-600">
                            Este resumen muestra cuánto se vendió por categoría en este turno,
                            para que puedas cuadrar el dinero de una sola vez. 💵
                        </p>
                    </div>

                    {{-- Botón para volver al sistema con confirmación --}}
                    <a href="{{ route('caja') }}"
                       onclick="return confirm('¿Seguro que deseas volver al sistema? Este resumen no se volverá a mostrar automáticamente.')"
                       class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow">
                        ⬅ Volver a la caja
                    </a>
                </div>

                @if($rows->isEmpty())
                    <p class="text-gray-500 text-sm mt-4">
                        No se registraron ventas en este turno.
                    </p>
                @else
                    <table class="w-full text-sm border-collapse mt-4">
                        <thead>
                            <tr class="bg-gray-100 text-xs uppercase text-gray-600">
                                <th class="border px-3 py-2 text-left">Categoría</th>
                                <th class="border px-3 py-2 text-right">Unidades</th>
                                <th class="border px-3 py-2 text-right">Total vendido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="border px-3 py-2">{{ $row->category_name }}</td>
                                    <td class="border px-3 py-2 text-right">
                                        {{ number_format($row->unidades_vendidas, 2) }}
                                    </td>
                                    <td class="border px-3 py-2 text-right font-semibold">
                                        L {{ number_format($row->total_vendido, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-100 font-bold">
                                <td class="border px-3 py-2 text-right" colspan="2">TOTAL</td>
                                <td class="border px-3 py-2 text-right">
                                    L {{ number_format($granTotal, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                <p class="text-xs text-gray-400 mt-4">
                    Nota: si sales de esta página, el sistema no te volverá a mostrar
                    automáticamente este resumen para este turno.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
