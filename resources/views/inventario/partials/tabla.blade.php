{{-- resources/views/inventario/partials/tabla.blade.php --}}
<div x-data="{ showImg:false, imgSrc:'' }"
     @open-image.window="showImg = true; imgSrc = $event.detail.src">

  <div class="overflow-x-auto bg-white rounded-xl shadow">
    <table class="min-w-full border-collapse">
      <thead class="bg-gray-50 text-left text-sm text-gray-600">
        <tr>
          <th class="px-4 py-3">Producto</th>
          <th class="px-4 py-3">Código</th>
          <th class="px-4 py-3">Proveedor</th>
          <th class="px-4 py-3 text-center">Stock</th>
          <th class="px-4 py-3 text-center">Mín.</th>
          <th class="px-4 py-3 text-right">P. Compra</th>
          <th class="px-4 py-3 text-right">P. Venta</th>
          <th class="px-4 py-3 text-right">Margen %</th>
          <th class="px-4 py-3 text-right">Acciones</th>
          <th class="px-4 py-3">Caducidad</th>
        </tr>
      </thead>

      <tbody class="divide-y">
      @forelse ($products as $p)
        @php
          // 👈 NUEVA LÓGICA: Calcular stock real para paquetes
          $stockDisplay = $p->stock;
          $stockLabel = '';
          
          if ($p->is_package && $p->parent_product_id && $p->units_per_package) {
              // Es un paquete: calcular cuántos paquetes se pueden armar
              $parentProduct = \App\Models\Product::find($p->parent_product_id);
              if ($parentProduct) {
                  $availablePackages = floor($parentProduct->stock / $p->units_per_package);
                  $stockDisplay = $availablePackages;
                  $stockLabel = ' paq.';
                  
                  // Bajo stock basado en el producto individual
                  $low = (int)$parentProduct->stock <= (int)$parentProduct->min_stock;
              } else {
                  $low = true; // Si no encuentra el producto padre, marcar como bajo
              }
          } else {
              // Producto normal
              $low = (int)$p->stock <= (int)$p->min_stock;
          }

          // --- Margen ---
          $margin = $p->price > 0
              ? round((($p->price - $p->purchase_price) / $p->price) * 100, 2)
              : 0;

          // --- Caducidad ---
          $exp = null;
          if (!empty($p->expires_at)) {
              $d    = \Carbon\Carbon::parse($p->expires_at);
              $now  = \Carbon\Carbon::today();
              $diff = $now->diffInDays($d, false);

              if ($diff < 0) {
                  $exp = ['text' => 'VENCIDO', 'class' => 'text-red-700 bg-red-100 border border-red-300'];
              } elseif ($diff <= 30) {
                  $exp = ['text' => 'Vence en '.$diff.' d', 'class' => 'text-amber-700 bg-amber-100 border border-amber-300'];
              } else {
                  $exp = ['text' => $d->format('d/m/Y'), 'class' => 'text-gray-600 bg-gray-100 border border-gray-200'];
              }
          }

          // --- Color de fila + color de celdas + borde izquierdo ---
          $rowClass  = '';
          $cellBg    = '';
          $leftBar   = '';

          if ($low) {
              $cellBg  = 'bg-red-100';
              $leftBar = 'border-l-4 border-red-500';
          } elseif ($exp && str_contains($exp['class'], 'red')) {
              $cellBg  = 'bg-red-100';
              $leftBar = 'border-l-4 border-red-500';
          } elseif ($exp && str_contains($exp['class'], 'amber')) {
              $cellBg  = 'bg-amber-50';
              $leftBar = 'border-l-4 border-amber-400';
          }
        @endphp

        <tr class="{{ $rowClass }}">
          {{-- Producto (imagen + nombre + badge bajo stock + badge paquete) --}}
          <td class="px-4 py-3 {{ $cellBg }} {{ $leftBar }} rounded-l-lg">
            <div class="flex items-center gap-3">

              {{-- Imagen --}}
              <div class="h-9 w-9 overflow-hidden rounded-lg ring-1 ring-gray-200 bg-white">
                @if($p->image_url)
                  <button
                    type="button"
                    class="h-full w-full focus:outline-none focus:ring-2 focus:ring-indigo-600 rounded"
                    aria-label="Ver imagen del producto"
                    @click="
                      showImg = true;
                      imgSrc  = '{{ $p->image_url }}';
                      $nextTick(() => $event.currentTarget.blur());
                    "
                    @mousedown.prevent
                    @focus="$event.currentTarget.blur()"
                    @mouseenter="$event.currentTarget.blur()"
                  >
                    <img src="{{ $p->image_url }}"
                         alt=""
                         aria-hidden="true"
                         draggable="false"
                         class="h-full w-full object-cover"
                         loading="lazy">
                  </button>
                @else
                  <div class="h-full w-full grid place-items-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </div>
                @endif
              </div>

              {{-- Nombre y badges --}}
              <div class="min-w-0">
                <div class="font-medium text-gray-900 truncate flex items-center gap-2">
                  {{ $p->name }}
                  
                  {{-- 👈 NUEVO: Badge si es paquete --}}
                  @if($p->is_package)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 border border-indigo-300 text-xs">
                      📦 Paquete
                    </span>
                  @endif
                  
                  @if($low)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-300 text-xs">
                      ⚠️ Bajo stock
                    </span>
                  @endif
                </div>
                
                {{-- 👈 NUEVO: Info del paquete --}}
                @if($p->is_package && $p->parentProduct)
                  <div class="text-xs text-gray-500 mt-0.5">
                    {{ $p->units_per_package }} × {{ $p->parentProduct->name }}
                  </div>
                @endif
              </div>

            </div>
          </td>

          <td class="px-4 py-3 text-gray-700 {{ $cellBg }}">{{ $p->barcode ?: '—' }}</td>

          <td class="px-4 py-3 text-gray-700 {{ $cellBg }}">{{ optional($p->provider)->name ?: '—' }}</td>

          {{-- 👈 MODIFICADO: Stock con label --}}
          <td class="px-4 py-3 text-sm text-center {{ $cellBg }} {{ $low ? 'text-red-700 font-semibold' : 'text-gray-700' }}">
            {{ $stockDisplay }}{{ $stockLabel }}
          </td>

          {{-- Mínimo --}}
          <td class="px-4 py-3 text-sm text-center text-gray-700 {{ $cellBg }}">{{ $p->min_stock }}</td>

          {{-- Precio compra / venta --}}
          <td class="px-4 py-3 text-right tabular-nums text-gray-700 {{ $cellBg }}">L {{ number_format($p->purchase_price, 2) }}</td>
          <td class="px-4 py-3 text-right tabular-nums text-gray-700 {{ $cellBg }}">L {{ number_format($p->price, 2) }}</td>
          <td class="px-4 py-3 text-right tabular-nums text-gray-700 {{ $cellBg }}">{{ number_format($margin, 2) }}%</td>

          {{-- Acciones --}}
          <td class="px-4 py-3 {{ $cellBg }}">
            <div class="flex justify-end">
              <a href="{{ route('productos.edit', $p) }}"
                 class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                     viewBox="0 0 24 24" fill="currentColor">
                  <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712Z"/>
                  <path d="M19.513 8.199 15.8 4.487 4.91 15.377a5.25 5.25 0 0 0-1.32 2.214l-.8 2.4a.75.75 0 0 0 .948.948l2.4-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z"/>
                </svg>
                Editar
              </a>
            </div>
          </td>

          {{-- Caducidad --}}
          <td class="px-4 py-3 {{ $cellBg }}">
            @if($exp)
              <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs {{ $exp['class'] }}">
                {{ $exp['text'] }}
              </span>
            @else
              <span class="text-gray-500">—</span>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="10" class="px-4 py-6 text-center text-gray-500">Sin productos</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <!-- Modal imagen -->
  <div x-show="showImg" x-cloak
       x-trap.noscroll="showImg"
       role="dialog" aria-modal="true"
       class="fixed inset-0 z-[1100] grid place-items-center bg-black/60 backdrop-blur-sm p-3"
       @click.self="showImg=false" @keydown.escape.window="showImg=false">
    <img :src="imgSrc"
         class="max-h-[85vh] max-w-[92vw] rounded-2xl shadow-2xl bg-white"
         alt="">
  </div>
</div>