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

       // === Sistema de pagos ===
       payments: [{ method: 'caja', amount: '', affects_cash: true, notes: '' }],

       add(){ this.items.push({ product_id:'', product_name:'', qty:1, unit_cost:'', resultados:[] }); },
       remove(i){ this.items.splice(i,1); },

       addPayment(){ this.payments.push({ method: 'caja', amount: '', affects_cash: true, notes: '' }); },
       removePayment(i){ if(this.payments.length > 1) this.payments.splice(i,1); },

       // Actualizar affects_cash cuando cambia el método
       updateAffectsCash(i){
         this.payments[i].affects_cash = this.payments[i].method === 'caja';
       },

       // Calcular total de la compra
       get totalCost(){
         return this.items.reduce((sum, item) => {
           const qty = parseFloat(item.qty) || 0;
           const cost = parseFloat(item.unit_cost) || 0;
           return sum + (qty * cost);
         }, 0);
       },

       // Calcular total de pagos
       get totalPayments(){
         return this.payments.reduce((sum, p) => {
           return sum + (parseFloat(p.amount) || 0);
         }, 0);
       },

       // Diferencia (pendiente o sobrepago)
       get paymentDifference(){
         return this.totalCost - this.totalPayments;
       },

       // Validar que los pagos cuadren
       get paymentsValid(){
         return Math.abs(this.paymentDifference) < 0.01;
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

      <!-- === DESGLOSE DE PAGOS (solo para compras) === -->
      <div x-show="type === 'in' && reason === 'purchase'" class="space-y-4">
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl shadow p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">💰 ¿Cómo vas a pagar esta compra?</h3>

          <!-- Resumen de totales -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 text-center">
              <div class="text-sm text-gray-600">Total de la compra</div>
              <div class="text-2xl font-bold text-purple-600" x-text="'L ' + totalCost.toFixed(2)"></div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
              <div class="text-sm text-gray-600">Total de pagos</div>
              <div class="text-2xl font-bold"
                   :class="paymentsValid ? 'text-green-600' : 'text-orange-600'"
                   x-text="'L ' + totalPayments.toFixed(2)"></div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
              <div class="text-sm text-gray-600">Diferencia</div>
              <div class="text-2xl font-bold"
                   :class="paymentsValid ? 'text-green-600' : (paymentDifference > 0 ? 'text-red-600' : 'text-orange-600')"
                   x-text="'L ' + Math.abs(paymentDifference).toFixed(2)"></div>
              <div class="text-xs" x-show="!paymentsValid">
                <span x-show="paymentDifference > 0" class="text-red-600">Falta pagar</span>
                <span x-show="paymentDifference < 0" class="text-orange-600">Sobrepago</span>
              </div>
            </div>
          </div>

          <!-- Tabla de pagos -->
          <div class="bg-white rounded-lg overflow-hidden shadow-sm">
            <table class="min-w-full text-sm">
              <thead class="bg-purple-100">
                <tr>
                  <th class="px-3 py-2 text-left">Método de pago</th>
                  <th class="px-3 py-2 text-right w-40">Monto (L)</th>
                  <th class="px-3 py-2 text-center w-32">Afecta caja</th>
                  <th class="px-3 py-2 text-left">Notas</th>
                  <th class="px-3 py-2 w-16"></th>
                </tr>
              </thead>
              <tbody>
                <template x-for="(pago, idx) in payments" :key="idx">
                  <tr class="border-t">
                    <td class="px-3 py-2">
                      <select x-model="pago.method"
                              @change="updateAffectsCash(idx)"
                              :name="`payments[${idx}][method]`"
                              class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="caja">💵 Efectivo de caja (sale del turno)</option>
                        <option value="efectivo_personal">💰 Efectivo personal (no sale del turno)</option>
                        <option value="credito">📝 A crédito</option>
                        <option value="transferencia">🏦 Transferencia</option>
                        <option value="tarjeta">💳 Tarjeta</option>
                      </select>
                    </td>
                    <td class="px-3 py-2 text-right">
                      <input type="number"
                             step="0.01"
                             min="0"
                             x-model="pago.amount"
                             :name="`payments[${idx}][amount]`"
                             placeholder="0.00"
                             class="w-full rounded-lg border-gray-300 text-right text-sm">
                    </td>
                    <td class="px-3 py-2 text-center">
                      <input type="hidden"
                             :name="`payments[${idx}][affects_cash]`"
                             :value="pago.affects_cash ? '1' : '0'">
                      <span x-show="pago.affects_cash"
                            class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">
                        ✓ Sí
                      </span>
                      <span x-show="!pago.affects_cash"
                            class="inline-block px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-medium">
                        ✗ No
                      </span>
                    </td>
                    <td class="px-3 py-2">
                      <input type="text"
                             x-model="pago.notes"
                             :name="`payments[${idx}][notes]`"
                             placeholder="Nota opcional..."
                             class="w-full rounded-lg border-gray-300 text-sm">
                    </td>
                    <td class="px-3 py-2 text-center">
                      <button type="button"
                              @click="removePayment(idx)"
                              x-show="payments.length > 1"
                              class="text-rose-600 hover:text-rose-800 text-sm">
                        Quitar
                      </button>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          <!-- Botón agregar pago -->
          <div class="mt-4">
            <button type="button"
                    @click="addPayment()"
                    class="px-4 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg text-sm font-medium">
              + Agregar otro método de pago
            </button>
          </div>

          <!-- Mensaje de validación -->
          <div x-show="!paymentsValid"
               class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
            <strong>⚠️ Atención:</strong> Los pagos deben sumar exactamente el total de la compra antes de guardar.
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button type="button" @click="add()" class="px-3 py-2 bg-gray-100 rounded-lg">+ Agregar fila</button>
        <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg"
                :disabled="(type === 'in' && reason === 'purchase' && !paymentsValid)"
                :class="{ 'opacity-50 cursor-not-allowed': (type === 'in' && reason === 'purchase' && !paymentsValid) }">
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
