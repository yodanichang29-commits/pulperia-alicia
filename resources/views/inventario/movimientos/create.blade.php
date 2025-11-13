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

  {{-- ERRORES DE VALIDACIÓN --}}
  @if ($errors->any())
    <div class="max-w-5xl mx-auto p-4">
      <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-lg">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">⚠️ Hay {{ count($errors) }} error(es) en el formulario:</h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  @if(session('success'))
    <div class="max-w-5xl mx-auto p-4">
      <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-lg">
        <p class="text-sm text-green-700">✅ {{ session('success') }}</p>
      </div>
    </div>
  @endif

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
       payments: [],
       hasTurnoAbierto: false,

       add(){ this.items.push({ product_id:'', product_name:'', qty:1, unit_cost:'', resultados:[] }); },
       remove(i){ this.items.splice(i,1); },

       // Pagos
       addPayment(){ this.payments.push({ amount:'', payment_method:'caja', affects_cash:true, notes:'' }); },
       removePayment(i){ this.payments.splice(i,1); },

       // Calcular total de la compra
       get totalCompra(){
         return this.items.reduce((sum, item) => {
           const qty = parseFloat(item.qty) || 0;
           const cost = parseFloat(item.unit_cost) || 0;
           return sum + (qty * cost);
         }, 0);
       },

       // Calcular total pagado
       get totalPagado(){
         return this.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
       },

       // Saldo pendiente
       get saldoPendiente(){
         return this.totalCompra - this.totalPagado;
       },

       // Verificar turno al cargar
       init(){
         fetch('/caja/shift/current')
           .then(r => r.json())
           .then(data => this.hasTurnoAbierto = data.shift !== null)
           .catch(() => this.hasTurnoAbierto = false);
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

      <div class="flex items-center gap-3">
        <button type="button" @click="add()" class="px-3 py-2 bg-gray-100 rounded-lg">+ Agregar fila</button>
      </div>

      {{-- SECCIÓN DE PAGOS --}}
      <div x-show="type === 'in' && reason === 'purchase'" class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl shadow-lg p-6 border border-emerald-200">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Forma de Pago (Opcional)
          </h3>
          <div class="text-right">
            <div class="text-sm text-gray-600">Total compra:</div>
            <div class="text-2xl font-bold text-gray-900">L <span x-text="totalCompra.toFixed(2)"></span></div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-4 mb-4 space-y-3">
          <template x-for="(pago, idx) in payments" :key="idx">
            <div class="flex gap-3 items-start p-3 bg-gray-50 rounded-lg border border-gray-200">
              {{-- Método de pago --}}
              <div class="flex-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Método</label>
                <select x-model="pago.payment_method"
                        @change="pago.affects_cash = ($event.target.value === 'caja')"
                        :name="`payments[${idx}][payment_method]`"
                        class="w-full rounded-lg border-gray-300 text-sm">
                  <option value="caja" :disabled="!hasTurnoAbierto">Efectivo de caja 💵</option>
                  <option value="efectivo_personal">Efectivo personal 💰</option>
                  <option value="credito">A crédito 📋</option>
                  <option value="transferencia">Transferencia 🏦</option>
                  <option value="tarjeta">Tarjeta 💳</option>
                </select>
                <div x-show="pago.payment_method === 'caja' && !hasTurnoAbierto" class="text-xs text-red-600 mt-1">
                  ⚠️ Sin turno abierto
                </div>
              </div>

              {{-- Monto --}}
              <div class="w-40">
                <label class="block text-xs font-medium text-gray-700 mb-1">Monto (L)</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       x-model="pago.amount"
                       :name="`payments[${idx}][amount]`"
                       class="w-full rounded-lg border-gray-300 text-sm text-right"
                       placeholder="0.00">
              </div>

              {{-- Afecta caja --}}
              <div class="w-32 pt-6">
                <label class="flex items-center text-sm">
                  <input type="checkbox"
                         x-model="pago.affects_cash"
                         :name="`payments[${idx}][affects_cash]`"
                         value="1"
                         class="rounded border-gray-300 text-indigo-600 mr-2">
                  <span class="text-xs text-gray-700">Afecta caja</span>
                </label>
              </div>

              {{-- Notas --}}
              <div class="flex-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Notas</label>
                <input type="text"
                       x-model="pago.notes"
                       :name="`payments[${idx}][notes]`"
                       class="w-full rounded-lg border-gray-300 text-sm"
                       placeholder="Opcional">
              </div>

              {{-- Botón eliminar --}}
              <div class="pt-6">
                <button type="button"
                        @click="removePayment(idx)"
                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                  Quitar
                </button>
              </div>

              {{-- Hidden input para affects_cash cuando no está marcado --}}
              <input type="hidden" :name="`payments[${idx}][affects_cash]`" :value="pago.affects_cash ? 1 : 0">
            </div>
          </template>

          {{-- Sin pagos --}}
          <div x-show="payments.length === 0" class="text-center py-8 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm mb-2">No hay pagos registrados</p>
            <p class="text-xs">La compra quedará pendiente de pago (a crédito)</p>
          </div>
        </div>

        {{-- Botón agregar pago --}}
        <div class="flex items-center justify-between">
          <button type="button"
                  @click="addPayment()"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Agregar pago
          </button>

          {{-- Resumen --}}
          <div class="text-right">
            <div class="flex items-center gap-4 text-sm">
              <div>
                <span class="text-gray-600">Total pagado:</span>
                <span class="font-semibold text-gray-900">L <span x-text="totalPagado.toFixed(2)"></span></span>
              </div>
              <div>
                <span class="text-gray-600">Saldo:</span>
                <span class="font-semibold"
                      :class="saldoPendiente > 0 ? 'text-amber-600' : (saldoPendiente < 0 ? 'text-red-600' : 'text-emerald-600')">
                  L <span x-text="saldoPendiente.toFixed(2)"></span>
                </span>
              </div>
            </div>
            <div x-show="saldoPendiente < 0" class="text-xs text-red-600 mt-1">
              ⚠️ El total pagado excede el total de la compra
            </div>
            <div x-show="saldoPendiente > 0" class="text-xs text-amber-600 mt-1">
              ℹ️ Pago parcial - Saldo pendiente
            </div>
            <div x-show="saldoPendiente === 0 && payments.length > 0" class="text-xs text-emerald-600 mt-1">
              ✓ Compra pagada completamente
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
          Guardar
        </button>
      </div>
    </form>
  </div>
</x-app-layout>
