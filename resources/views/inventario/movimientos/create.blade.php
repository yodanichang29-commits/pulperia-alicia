<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800">Nuevo movimiento</h2>

      <a href="{{ route('ingresos.index') }}"
         class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
        <!-- Icono de flecha -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.8" stroke="currentColor" class="w-5 h-5 mr-1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Volver
      </a>
    </div>
  </x-slot>
  

  

 <div class="max-w-5xl mx-auto p-4"
     x-data="{
       type: 'in',
       reason: 'purchase',
       moved_at: '{{ now()->toDateString() }}',
       provider_id: '',
       provider_name: '',
       provResultados: [],
       supplier: '', // puedes dejarlo si lo usas como etiqueta
       reference: '',
       notes: '',
       items: [{ product_id:'', product_name:'', qty:1, unit_cost:'', resultados:[] }],
       paid_from_cash: 0,
       paid_from_outside: 0,

       add(){ this.items.push({ product_id:'', product_name:'', qty:1, unit_cost:'', resultados:[] }); },
       remove(i){ this.items.splice(i,1); },

       // === Calcular total de la compra ===
       get totalCompra() {
         return this.items.reduce((sum, item) => {
           const qty = parseFloat(item.qty) || 0;
           const cost = parseFloat(item.unit_cost) || 0;
           return sum + (qty * cost);
         }, 0);
       },

       // === Calcular total pagado ===
       get totalPagado() {
         const fromCash = parseFloat(this.paid_from_cash) || 0;
         const fromOutside = parseFloat(this.paid_from_outside) || 0;
         return fromCash + fromOutside;
       },

       // === Calcular saldo pendiente ===
       get saldoPendiente() {
         return Math.max(0, this.totalCompra - this.totalPagado);
       },

       // === Validar que los pagos no excedan el total ===
       get excedePago() {
         return this.totalPagado > this.totalCompra;
       },

       // === Autocompletar proveedor ===
       buscarProveedor(term){
         if (!term || term.length < 2) { this.provResultados = []; return; }
         fetch(`/proveedores/buscar?q=${encodeURIComponent(term)}`)
           .then(r => r.json())
           .then(d => this.provResultados = d)
           .catch(() => this.provResultados = []);
       },
       seleccionarProveedor(p){
         this.provider_id = p.id;
         this.provider_name = p.name;
         this.provResultados = [];
       },

       // === Autocompletar producto (ya lo tenías) ===
       buscarProducto(i, term) {
         if (!term || term.length < 2) { this.items[i].resultados = []; return; }
         fetch(`/productos/buscar?q=${encodeURIComponent(term)}`)
           .then(res => res.json())
           .then(data => this.items[i].resultados = data)
           .catch(() => this.items[i].resultados = []);
       },
       seleccionarProducto(i, prod) {
         this.items[i].product_id   = prod.id;
         this.items[i].product_name = prod.name;
         this.items[i].resultados   = [];
         if (this.type === 'in') { this.items[i].unit_cost = prod.purchase_price ?? ''; }
       }
     }"
>



{{-- Mensajes de error de validación --}}
@if ($errors->any())
  <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
    <p class="font-semibold mb-1">Hay algunos problemas con lo que ingresaste:</p>
    <ul class="list-disc list-inside space-y-0.5">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif


<form method="POST" action="{{ route('ingresos.store') }}" class="space-y-6">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm">Tipo</label>
          <select x-model="type" name="type" class="w-full rounded-lg border-gray-300">
            <option value="in">Entrada</option>
            <option value="out">Salida</option>
          </select>
        </div>

        <div>
          <label class="block text-sm">Motivo</label>
          <select x-model="reason" name="reason" class="w-full rounded-lg border-gray-300">
            <template x-if="type==='in'">
              <optgroup label="Entradas">
                <option value="purchase">Compra</option>
                <option value="adjust_in">Ajuste (+)</option>
              </optgroup>
            </template>
            <template x-if="type==='out'">
              <optgroup label="Salidas">
                <option value="waste">Merma</option>
                <option value="damaged">Dañado</option>
                <option value="expired">Vencido</option>
                <option value="internal_use">Uso interno</option>
                <option value="adjust_out">Ajuste (-)</option>
              </optgroup>
            </template>
          </select>
        </div>

        <div>
          <label class="block text-sm">Fecha</label>
          <input type="date" x-model="moved_at" name="moved_at" class="w-full rounded-lg border-gray-300">
        </div>

        <div>
          <label class="block text-sm">Factura/Referencia</label>
          <input type="text" x-model="reference" name="reference" class="w-full rounded-lg border-gray-300" placeholder="# factura">
        </div>

     <div class="md:col-span-2">
  <label class="block text-sm">Proveedor <span class="text-gray-400" x-show="type==='in' && reason==='purchase'">(obligatorio)</span></label>

  <div class="relative">
    <input type="text"
           x-model="provider_name"
           @input.debounce.300ms="buscarProveedor(provider_name)"
           class="w-full rounded-lg border-gray-300"
           :placeholder="(type==='in' && reason==='purchase') ? 'Busca y selecciona el proveedor…' : 'Proveedor (opcional)'">

    <!-- Lista de coincidencias -->
    <div x-show="provResultados.length"
         class="absolute bg-white border rounded-lg shadow mt-1 max-h-48 overflow-y-auto w-full z-10">
      <template x-for="p in provResultados" :key="p.id">
        <div @click="seleccionarProveedor(p)"
             class="px-3 py-2 hover:bg-indigo-50 cursor-pointer">
          <span x-text="p.name"></span>
        </div>
      </template>
    </div>
  </div>

  <!-- Enviar el ID real -->
  <input type="hidden" name="provider_id" x-model="provider_id">
</div>


        <div class="md:col-span-4">
          <label class="block text-sm">Notas</label>
          <textarea x-model="notes" name="notes" class="w-full rounded-lg border-gray-300" rows="2"></textarea>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 text-left">Producto (ID)</th>
              <th class="px-3 py-2 text-right w-32">Cantidad</th>
              <th class="px-3 py-2 text-right w-40">Costo unitario (L) <span class="text-gray-500" x-show="type==='out'">(auto)</span></th>
              <th class="px-3 py-2 w-16"></th>
            </tr>
          </thead>
          <tbody>
            <template x-for="(row,i) in items" :key="i">
              <tr class="border-t">
                <td class="px-3 py-2">
                  <div class="relative">
  <input type="text"
         x-model="row.product_name"
         @input.debounce.300ms="buscarProducto(i, row.product_name)"
         class="w-52 rounded-lg border-gray-300"
         placeholder="Buscar producto...">

  <!-- Resultados -->
  <div x-show="row.resultados?.length"
       class="absolute bg-white border rounded-lg shadow mt-1 max-h-40 overflow-y-auto w-52 z-10">
      <template x-for="prod in row.resultados" :key="prod.id">
          <div @click="seleccionarProducto(i, prod)"
               class="px-2 py-1 hover:bg-indigo-100 cursor-pointer text-sm">
              <span x-text="prod.name"></span>
              <span class="text-gray-500" x-text="'(' + prod.codigo + ')'"></span>
          </div>
      </template>
  </div>
</div>

<!-- Campo oculto real -->
<input type="hidden" :name="`items[${i}][product_id]`" x-model="row.product_id">

                </td>
                <td class="px-3 py-2 text-right">
                  <input type="number" min="1" x-model="row.qty" :name="`items[${i}][qty]`"
                         class="w-28 rounded-lg border-gray-300 text-right">
                </td>
                <td class="px-3 py-2 text-right">
                  <input :disabled="type==='out'" type="number" step="0.01" min="0"
                         x-model="row.unit_cost" :name="`items[${i}][unit_cost]`"
                         class="w-36 rounded-lg border-gray-300 text-right">
                </td>
                <td class="px-3 py-2 text-right">
                  <button type="button" @click="remove(i)" class="text-rose-600">Quitar</button>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- ============================================
           SECCION DE PAGOS (solo para compras)
           ============================================ -->
      <div x-show="type === 'in' && reason === 'purchase'"
           class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-lg p-6 border border-indigo-200">

        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 mr-2 text-indigo-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
          </svg>
          Información de Pago
        </h3>

        <!-- Resumen del total -->
        <div class="mb-6 p-4 bg-white rounded-lg border border-gray-200">
          <div class="flex justify-between items-center">
            <span class="text-gray-700 font-medium">Total de la compra:</span>
            <span class="text-2xl font-bold text-gray-900" x-text="'L ' + totalCompra.toFixed(2)"></span>
          </div>
        </div>

        <!-- Campos de pago -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <!-- Pago desde caja -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              💵 Pago desde caja del turno
              <span class="text-gray-500 text-xs font-normal">(efectivo)</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">L</span>
              <input type="number"
                     step="0.01"
                     min="0"
                     x-model="paid_from_cash"
                     name="paid_from_cash"
                     class="w-full pl-8 pr-4 py-2 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                     :class="excedePago ? 'border-red-500 bg-red-50' : ''"
                     placeholder="0.00">
            </div>
            <p class="mt-1 text-xs text-gray-600">Monto que se descontará del turno de caja actual</p>
          </div>

          <!-- Pago desde fondos externos -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              🏦 Pago desde fondos externos
              <span class="text-gray-500 text-xs font-normal">(banco, dueño, etc.)</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">L</span>
              <input type="number"
                     step="0.01"
                     min="0"
                     x-model="paid_from_outside"
                     name="paid_from_outside"
                     class="w-full pl-8 pr-4 py-2 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                     :class="excedePago ? 'border-red-500 bg-red-50' : ''"
                     placeholder="0.00">
            </div>
            <p class="mt-1 text-xs text-gray-600">Dinero que NO proviene de la caja del turno</p>
          </div>
        </div>

        <!-- Resumen de pagos -->
        <div class="bg-white rounded-lg p-4 border border-gray-200 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Total pagado:</span>
            <span class="font-semibold"
                  :class="excedePago ? 'text-red-600' : 'text-gray-900'"
                  x-text="'L ' + totalPagado.toFixed(2)"></span>
          </div>
          <div class="flex justify-between text-sm border-t pt-2">
            <span class="text-gray-600">Saldo pendiente:</span>
            <span class="font-semibold"
                  :class="saldoPendiente > 0 ? 'text-amber-600' : 'text-green-600'"
                  x-text="'L ' + saldoPendiente.toFixed(2)"></span>
          </div>

          <!-- Mensaje de advertencia si excede -->
          <div x-show="excedePago" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            ⚠️ El total pagado no puede ser mayor al total de la compra
          </div>

          <!-- Mensaje informativo si queda saldo -->
          <div x-show="saldoPendiente > 0 && !excedePago && totalCompra > 0"
               class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
            ℹ️ La compra quedará con saldo pendiente (fiado)
          </div>

          <!-- Mensaje de éxito si está totalmente pagada -->
          <div x-show="saldoPendiente <= 0.01 && totalPagado > 0 && !excedePago"
               class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            ✓ La compra quedará completamente pagada
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button type="button" @click="add()" class="px-3 py-2 bg-gray-100 rounded-lg">+ Agregar fila</button>
        <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                :disabled="excedePago"
                :class="excedePago ? 'opacity-50 cursor-not-allowed' : ''">
          Guardar
        </button>
      </div>

      {{-- Hidden inputs para enviar header vía POST --}}
      <input type="hidden" name="supplier"  :value="supplier">
      <input type="hidden" name="reference" :value="reference">
      <input type="hidden" name="notes"     :value="notes">
    </form>
  </div>
</x-app-layout>
