<x-app-layout>
  {{-- ✅ SweetAlert2 para notificaciones bonitas --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  {{-- Evitar parpadeos hasta que Alpine cargue --}}
  <style>[x-cloak]{display:none!important}</style>

  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        🧾 Punto de Venta (táctil) — <span class="text-blue-700">Pulpería Alicia</span>
      </h2>
      <button type="button"
              class="text-sm text-blue-700 underline"
              onclick="document.documentElement.requestFullscreen?.()">
        Pantalla completa
      </button>
    </div>
  </x-slot>

  {{-- ==== Banner + Modales (MISMO COMPONENTE) ==== --}}
  <div x-data="shift()" x-init="init()" class="mb-4" :class="loading ? 'animate-pulse' : ''">
    <!-- Banner -->
    <template x-if="!state.current">
      <div class="flex items-center justify-between p-3 rounded-xl border border-dashed border-amber-300 bg-amber-50">
        <div class="text-amber-800">
          <span class="font-semibold">No tienes un turno abierto.</span>
          <span class="ml-1">Abre un turno para registrar ventas.</span>
        </div>
        <button @click="openModal=true" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Abrir turno</button>
      </div>
    </template>

    <template x-if="state.current">
      <div class="flex items-center justify-between p-3 rounded-xl border bg-emerald-50 border-emerald-200">
        <div class="text-emerald-800">
          <span class="font-semibold">Turno abierto</span>
          <span class="mx-2">|</span>
          <span x-text="'Inicio: ' + (state.current?.opened_at || '')"></span>
          <span class="mx-2">|</span>
          <span x-text="'Fondo inicial: L ' + Number(state.current?.opening_float || 0).toFixed(2)"></span>
        </div>
        <div class="flex items-center gap-2">
          <button @click="refreshAll()" :disabled="loading" class="px-4 py-2 rounded-lg bg-white border hover:bg-slate-50 disabled:opacity-50">
            <span x-show="!loading">Actualizar</span>
            <span x-show="loading" class="inline-flex items-center gap-2">Actualizando…</span>
          </button>
          <span class="text-xs text-gray-500"
                x-text="lastUpdated ? 'Actualizado ' + new Date(lastUpdated).toLocaleTimeString('es-HN',{hour12:false}) : ''"></span>
          <button @click="closeModal=true; refreshSummary()" class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-black">
            Cerrar turno
          </button>
        </div>
      </div>
    </template>

    {{-- Modal Abrir turno --}}
 {{-- Modal Abrir turno --}}
<template x-teleport="body">
<div x-cloak x-show="openModal" class="fixed inset-0 z-50 grid place-items-center bg-black/40">
  <div @click.outside="openModal=false" class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl">
    <h3 class="text-lg font-semibold mb-3">🔓 Abrir turno</h3>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
      <p class="text-sm text-blue-800">
        <strong>⚠️ Importante:</strong> Debes contar el efectivo que tienes en caja e ingresar el monto inicial antes de comenzar.
      </p>
    </div>
    
    <label class="block text-sm font-semibold text-gray-700 mb-1">
      Monto inicial en caja <span class="text-red-500">*</span>
    </label>
    <input 
      type="number" 
      step="0.01" 
      min="0.01" 
      x-model="form.opening_float" 
      required
      class="w-full rounded-lg border-2 border-gray-300 px-3 py-2 mb-3 focus:border-blue-500 focus:ring focus:ring-blue-200" 
      placeholder="Ejemplo: 500.00"
      x-ref="openingFloatInput">
    
    <label class="block text-sm text-gray-600 mb-1">Notas (opcional)</label>
    <textarea x-model="form.notes" class="w-full rounded-lg border px-3 py-2 mb-4" rows="2" placeholder="Ej: Turno de mañana"></textarea>
    
    <div class="flex justify-end gap-2">
      <button @click="openModal=false" class="px-3 py-2 rounded-lg border hover:bg-gray-50">Cancelar</button>
      <button 
        :disabled="loading || !form.opening_float || form.opening_float <= 0" 
        @click="openShift" 
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
        <span x-show="!loading">✓ Abrir turno</span>
        <span x-show="loading">Abriendo...</span>
      </button>
    </div>
  </div>
</div>
</template>

    {{-- Modal Cerrar turno --}}
    <template x-teleport="body">
      <div x-cloak x-show="closeModal" class="fixed inset-0 z-50 grid place-items-center bg-black/40">
        <div @click.outside="closeModal=false" class="w-full max-w-lg rounded-2xl bg-white p-5 shadow-xl">
          <h3 class="text-lg font-semibold mb-3">Cerrar turno</h3>
          <div class="rounded-lg border p-3 mb-3">
            <div class="text-sm text-gray-600 mb-1">Resumen por método</div>
            <template x-for="(row, method) in summary.by_payment" :key="method">
              <div class="flex justify-between text-sm py-1 border-b last:border-b-0">
                <span class="uppercase" x-text="methodLabel(method)"></span>
                <span x-text="'L ' + Number(row.total ?? 0).toFixed(2)"></span>
              </div>
            </template>



<!-- ============================================ -->
<!-- ✅ NUEVA LÍNEA: DEVOLUCIONES -->
<!-- ============================================ -->
<div 
  x-show="Number(summary.devoluciones ?? 0) > 0" 
  class="flex justify-between text-sm py-2 border-t mt-2 pt-2"
>
  <span class="font-semibold text-red-600 uppercase">
    🔄 DEVOLUCIONES
  </span>
  <span class="font-semibold text-red-600 tabular-nums">
    L -<span x-text="Number(summary.devoluciones ?? 0).toFixed(2)"></span>
  </span>
</div>



            <div class="flex justify-between">
              <span>ABONOS (efectivo)</span>
              <span>L <span x-text="Number(summary.abonos_by_method?.efectivo || 0).toFixed(2)"></span></span>
            </div>
            <div class="flex justify-between">
              <span>ABONOS (tarjeta)</span>
              <span>L <span x-text="Number(summary.abonos_by_method?.tarjeta || 0).toFixed(2)"></span></span>
            </div>
            <div class="flex justify-between">
              <span>ABONOS (transferencia)</span>
              <span>L <span x-text="Number(summary.abonos_by_method?.transferencia || 0).toFixed(2)"></span></span>
            </div>
            <div class="flex justify-between font-semibold">
              <span>Total abonos</span>
              <span>L <span x-text="Number(summary.abonos_total || 0).toFixed(2)"></span></span>
            </div>
         <div class="border-t mt-2 pt-3">
  <div class="flex justify-between text-sm font-semibold">
    <span>Efectivo esperado</span>
    <span class="text-lg" x-text="'L ' + Number(summary.expected_cash ?? 0).toFixed(2)"></span>
  </div>
  <!-- ✅ NUEVA NOTA EXPLICATIVA -->
  <div class="text-xs text-gray-500 mt-1">
    (Fondo inicial + Ventas efectivo + Abonos - Devoluciones)
  </div>
</div>
          </div>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
  <p class="text-sm text-amber-800">
    <strong>⚠️ Importante:</strong> Cuenta todo el efectivo que tienes en caja y anota el total exacto.
  </p>
</div>

<label class="block text-sm font-semibold text-gray-700 mb-1">
  Conteo total de efectivo <span class="text-red-500">*</span>
</label>
<input 
  type="number" 
  step="0.01" 
  min="0" 
  x-model="form.closing_cash_count" 
  required
  class="w-full rounded-lg border-2 border-gray-300 px-3 py-2 mb-3 focus:border-blue-500 focus:ring focus:ring-blue-200" 
  placeholder="Ejemplo: 1250.00"
  x-ref="closingCashInput">
  
<div x-show="form.closing_cash_count && summary.expected_cash" class="mb-3">
  <div class="flex justify-between text-sm py-2 border-t border-b">
    <span class="font-medium">Efectivo esperado:</span>
    <span class="font-mono" x-text="'L ' + Number(summary.expected_cash ?? 0).toFixed(2)"></span>
  </div>
  <div class="flex justify-between text-sm py-2 border-b">
    <span class="font-medium">Efectivo contado:</span>
    <span class="font-mono" x-text="'L ' + Number(form.closing_cash_count ?? 0).toFixed(2)"></span>
  </div>
  <div class="flex justify-between text-sm py-2 font-bold"
       :class="(Number(form.closing_cash_count||0) - Number(summary.expected_cash||0)) >= 0 ? 'text-green-700' : 'text-red-700'">
    <span>Diferencia:</span>
    <span class="font-mono" x-text="'L ' + (Number(form.closing_cash_count||0) - Number(summary.expected_cash||0)).toFixed(2)"></span>
  </div>
</div>
          <label class="block text-sm text-gray-600 mb-1">Notas (opcional)</label>
          <textarea x-model="form.notes" class="w-full rounded-lg border px-3 py-2 mb-4" rows="2"></textarea>
          <div class="flex justify-end gap-2">
            <button @click="closeModal=false" class="px-3 py-2 rounded-lg border">Cancelar</button>
<button 
  :disabled="loading || !form.closing_cash_count || form.closing_cash_count < 0" 
  @click="closeShift" 
  class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-black disabled:opacity-50 disabled:cursor-not-allowed">
  <span x-show="!loading">🔒 Confirmar cierre</span>
  <span x-show="loading">Cerrando...</span>
</button>          </div>
        </div>
      </div>
    </template>
  </div>

  @push('scripts')
  <script>
  function shift() {
    return {
      state: { current: null },
      summary: { by_payment:{}, expected_cash: 0 },
      form: { opening_float:'', notes:'', closing_cash_count:'' },
      openModal:false, closeModal:false,
      loading:false, lastUpdated:null,

      methodLabel(m) {
        const map = {
          cash:     'EFECTIVO',
          card:     'TARJETA',
          transfer: 'TRANSFERENCIA',
          credit:   'CRÉDITO',
        };
        const k = (m ?? '').toString().toLowerCase();
        return map[k] ?? k.toUpperCase();
      },

      init(){
        this.fetchCurrent();
        window.addEventListener('sale:registered', async () => {
          await this.fetchCurrent(); await this.refreshSummary();
        });
        window.addEventListener('keydown', e => { if (e.key.toLowerCase()==='r') this.refreshAll(); });
      },

      toast(msg){
        const id = 't-'+Date.now();
        document.body.insertAdjacentHTML('beforeend',
          `<div id="${id}" class="fixed bottom-5 left-1/2 -translate-x-1/2
            bg-slate-800 text-white px-4 py-2 rounded-lg shadow">${msg}</div>`);
        setTimeout(()=>document.getElementById(id)?.remove(), 1000);
      },

      async refreshAll(){
        this.loading = true;
        try{
          await this.fetchCurrent();
          await this.refreshSummary();
          this.toast('Actualizado');
        } catch(e){
          console.error(e); this.toast('No se pudo actualizar');
        } finally {
          this.loading = false;
          this.lastUpdated = new Date();
        }
      },

      async fetchCurrent(){
        try{
          const r = await fetch('{{ route('caja.shift.current') }}', {
            headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'Cache-Control':'no-store' },
            credentials:'same-origin', cache:'no-store'
          });
          const j = await r.json().catch(()=>({shift:null}));
          this.state.current = j?.shift ?? null;
        }catch(e){
          console.error('fetchCurrent error', e);
          this.state.current = null;
        }
      },

      async refreshSummary(){
        try{
          const r = await fetch('{{ route('caja.shift.summary') }}', {
            headers:{
              'Accept':'application/json',
              'X-Requested-With':'XMLHttpRequest',
              'Cache-Control':'no-store'
            },
            credentials:'same-origin',
            cache:'no-store'
          });
          if (!r.ok) {
            this.summary = { by_payment:{}, expected_cash:0 };
            return;
          }
          this.summary = await r.json();
        }catch(e){
          console.error('summary error', e);
          this.summary = { by_payment:{}, expected_cash:0 };
        }
      },

      async openShift(){
  // Validar antes de enviar
  if (!this.form.opening_float || this.form.opening_float <= 0) {
    alert('⚠️ Debes ingresar el monto inicial de la caja (mayor a 0)');
    return;
  }
  
  this.loading = true;
  try{
    const r = await fetch('{{ route('caja.shift.open') }}', {
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'Accept':'application/json',
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Cache-Control':'no-store'
      },
      credentials:'same-origin',
      cache:'no-store',
      body: JSON.stringify({
        opening_float: Number(this.form.opening_float||0),
        notes: this.form.notes||''
      })
    });
    
    if(!r.ok){
      const err = await r.json().catch(()=>null);
      throw new Error(err?.message || 'Error al abrir turno');
    }
    
    this.openModal = false;
    this.form.opening_float = '';
    this.form.notes = '';
    await this.fetchCurrent();
    alert('✅ Turno abierto correctamente');
  }catch(e){
    alert('❌ ' + (e.message || 'Error al abrir turno'));
  }finally{
    this.loading = false;
  }
},

      async closeShift(){
        this.loading = true;
        try{
          const r = await fetch('{{ route('caja.shift.close') }}', {
            method:'POST',
            headers:{
              'Content-Type':'application/json',
              'Accept':'application/json',
              'X-Requested-With':'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Cache-Control':'no-store'
            },
            credentials:'same-origin',
            cache:'no-store',
            body: JSON.stringify({
              closing_cash_count: Number(this.form.closing_cash_count||0),
              notes: this.form.notes||''
            })
          });

          const text = await r.text(); let data; try{ data=JSON.parse(text);}catch{}
          if(!r.ok){ throw new Error((data&&data.message)||text||'Error al cerrar turno'); }

          this.state.current = null;
          this.closeModal = false;
          this.form.closing_cash_count = '';
          this.form.notes = '';
          this.summary = { by_payment:{}, expected_cash:0 };

          await this.fetchCurrent();
          alert('Turno cerrado');
        }catch(e){
          alert(e.message || 'Error al cerrar turno');
          console.error(e);
        }finally{
          this.loading = false;
        }
      },
    }
  }
  </script>
  @endpush

  <div x-data="pos()" x-init="init()" @close-new-client.window="openNewClient=false" class="bg-gray-100 min-h-screen py-4">

    {{-- Categorías + búsqueda --}}
    <div class="max-w-7xl mx-auto flex flex-wrap items-center gap-2 mb-4 px-4">
      <button @click="setCat(null)"
              :class="activeCat===null ? 'bg-blue-700 text-white shadow' : 'bg-white text-gray-800 border border-blue-200'"
              class="px-4 py-2 rounded-full font-semibold">
        Todo
      </button>

      @foreach($categories as $cat)
        <button @click="setCat(@js($cat))"
                :class="activeCat===@js($cat) ? 'bg-blue-700 text-white shadow-lg' : 'bg-white text-gray-800 border border-blue-200'"
                class="px-4 py-2 rounded-full font-semibold transition">
          {{ $cat }}
        </button>
      @endforeach

      <div class="ml-auto">
        <input x-model="search" type="search" placeholder="Buscar productos…"
               class="rounded-xl border border-blue-300 px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-sm"
               inputmode="search">
      </div>
    </div>

    {{-- BOTONES DE GESTIÓN DE VENTAS --}}
    <div class="max-w-7xl mx-auto px-4 mb-4">
        <div class="flex flex-wrap gap-3">
            <button type="button" 
                    @click="holdSale()"
                    class="inline-flex items-center px-5 py-3 bg-yellow-500 hover:bg-yellow-600 
                           text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl
                           border-2 border-yellow-600">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" 
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-lg">⏸️ EN ESPERA</span>
            </button>
            
            <button type="button" 
                    @click="showPendingSales()"
                    class="inline-flex items-center px-5 py-3 bg-blue-600 hover:bg-blue-700 
                           text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl
                           border-2 border-blue-700">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" 
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-lg">📋 VENTAS EN ESPERA</span>
                <span id="pending-count" 
                      class="ml-2 bg-white text-blue-700 px-2.5 py-1 rounded-full text-sm font-black shadow-inner">
                    0
                </span>
            </button>
            
            <button type="button" 
                    @click="openReturnModal()"
                    class="inline-flex items-center px-5 py-3 bg-red-600 hover:bg-red-700 
                           text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl
                           border-2 border-red-700">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" 
                          d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                <span class="text-lg">🔄 DEVOLUCIÓN</span>
            </button>
        </div>
    </div>

    {{-- Layout principal --}}
    <div class="max-w-7xl mx-auto grid md:grid-cols-12 gap-4 px-4">

      {{-- IZQUIERDA: Ticket + Pago --}}
      <div class="md:col-span-4 space-y-4">

        {{-- Ticket --}}
        <div class="rounded-2xl overflow-hidden bg-white shadow-lg border border-blue-200">
          <div class="px-4 py-3 bg-blue-600 text-white flex items-center justify-between">
            <div class="font-semibold text-lg">🧺 Ticket</div>
            <button @click="clear()" class="text-xs px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold">
              Vaciar
            </button>
          </div>

          <div class="max-h-[46vh] overflow-y-auto divide-y divide-gray-200">
            <template x-if="!cart.length">
              <div class="p-4 text-sm text-gray-500">Toca un producto para agregarlo</div>
            </template>

            <template x-for="(item, i) in cart" :key="item.id">
              <div class="px-3 py-2 flex items-center gap-2 hover:bg-blue-50">
                <div class="text-xs w-10 shrink-0 text-gray-600 font-bold" x-text="item.qty + '×'"></div>
                <div class="flex-1">
                  <div class="text-sm font-semibold text-gray-800" x-text="item.name"></div>
                  <div class="text-xs text-gray-500" x-text="money(item.price)"></div>
                </div>
                <div class="w-20 text-right font-semibold text-blue-700" x-text="money(item.qty*item.price)"></div>
                <div class="flex gap-1 ml-2">
                  <button class="px-2.5 py-1 rounded-md bg-blue-500 text-white hover:bg-blue-600 font-bold" @click="dec(i)">−</button>
                  <button class="px-2.5 py-1 rounded-md bg-blue-600 text-white hover:bg-blue-700 font-bold" @click="inc(i)">+</button>
                  <button class="px-2.5 py-1 rounded-md bg-red-600 text-white hover:bg-red-700 font-bold" @click="remove(i)">×</button>
                </div>
              </div>
            </template>
          </div>

          <div class="px-4 py-4 bg-gradient-to-r from-blue-700 to-indigo-700 text-white">
            <div class="flex justify-between text-sm/relaxed opacity-90">
              <span>Artículos</span><span x-text="itemsCount()"></span>
            </div>
            <div class="mt-1 flex justify-between text-3xl font-extrabold tracking-wide">
              <span>Total</span><span x-text="money(grandTotal())"></span>
            </div>
          </div>
        </div>

        {{-- Pago --}}
        <div class="rounded-2xl bg-white p-4 shadow-lg border border-blue-200">
          <label class="text-sm font-semibold block mb-2 text-gray-700">Método de pago</label>

          <div class="grid grid-cols-2 gap-3 mb-3">
            <button :class="payBtn('cash')"     class="py-3 rounded-xl font-semibold" @click="payment='cash'">Efectivo</button>
            <button :class="payBtn('card')"     class="py-3 rounded-xl font-semibold" @click="payment='card'">Tarjeta</button>
            <button :class="payBtn('transfer')" class="py-3 rounded-xl font-semibold" @click="payment='transfer'">Transferencia</button>
            <button :class="payBtn('credit')"   class="py-3 rounded-xl font-semibold" @click="payment='credit'">Crédito</button>
          </div>

          <div x-show="payment==='card'" x-cloak class="mt-1 flex items-center gap-3" x-transition.opacity>
            <label class="text-sm text-gray-600">Comisión tarjeta (%)</label>
            <input type="number" min="0" step="0.1" x-model.number="feePct"
                   class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-right"
                   placeholder="0.0" inputmode="decimal">
            <div class="text-sm text-gray-600">
              Comisión: <span class="font-semibold" x-text="money(feeAmount())"></span>
            </div>
          </div>

          <template x-if="payment==='credit'">
            <div class="mt-4 space-y-3" x-transition.opacity>
              <div class="grid grid-cols-[1fr_auto] gap-2">
                <input x-model="clientQuery"
                       @input.debounce.300ms="searchClients"
                       type="search"
                       placeholder="Buscar cliente (nombre o teléfono)"
                       class="w-full rounded-lg border px-3 py-2"
                       inputmode="search">
                <button type="button"
                        @click="openNewClient=true; $nextTick(()=>$refs.cliname?.focus())"
                        class="px-3 py-2 rounded-lg border bg-white shadow-sm hover:bg-gray-50">
                  Nuevo
                </button>
                <button type="button"
                  @click="$dispatch('abono-open')"
                  class="px-4 py-2 rounded-xl bg-amber-600 text-white font-semibold">
                  Cobrar abono
                </button>
              </div>

              <div x-show="clientResults.length" x-cloak class="bg-white border rounded-lg max-h-48 overflow-auto">
                <template x-for="c in clientResults" :key="c.id">
                  <button class="w-full text-left px-3 py-2 hover:bg-blue-50"
                          @click="client=c; clientResults=[]; clientQuery=''">
                    <span class="font-medium" x-text="c.name"></span>
                    <span class="text-xs text-gray-500 ml-2" x-text="c.phone || ''"></span>
                  </button>
                </template>
              </div>

              <template x-if="client">
                <div class="text-sm text-gray-700">
                  Cliente: <span class="font-semibold" x-text="client.name"></span>
                  <span class="text-xs text-gray-500" x-text="client.phone ? ' · '+client.phone : ''"></span>
                  <button class="text-blue-600 ml-2" @click="client=null">Cambiar</button>
                </div>
              </template>

              <div class="flex items-center gap-3">
                <label class="text-sm text-gray-600">Vence:</label>
                <input type="date" x-model="dueDate" class="rounded-lg border px-3 py-2">
              </div>
            </div>
          </template>

          <button
            class="mt-4 w-full py-4 rounded-2xl bg-blue-600 text-white text-xl font-extrabold tracking-wide shadow-lg hover:bg-blue-700 transition disabled:bg-blue-300"
            :disabled="payment==='cash' && !hasShift"
            @click="payment==='cash' ? openCashModal() : pay()">
            💰 Cobrar <span x-text="money(grandTotal())"></span>
          </button>

        </div>
      </div>

      {{-- DERECHA: Grid de productos --}}
      <div class="md:col-span-8">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          @foreach($products as $p)
            <button
              @click="add({ id: {{ $p->id }}, name: @js($p->name), price: {{ $p->price }}, cat: @js($p->category) })"
              x-show="showProduct(@js($p->category), @js($p->name))"
              class="relative group rounded-2xl bg-white border border-gray-200 hover:border-blue-400 overflow-hidden text-left shadow hover:shadow-lg transition">
              <div class="relative rounded-2xl overflow-hidden bg-white grid place-items-center">
                <div class="w-full h-40 md:h-44 p-2">
                  <img
                    src="{{ $p->image_url }}"
                    alt="{{ $p->name }}"
                    class="w-full h-full object-contain"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder.png') }}';"
                  >
                </div>
              </div>
              <div class="p-3">
                <span class="inline-block text-[10px] px-2 py-0.5 rounded-full mb-1 font-semibold bg-blue-100 text-blue-800 uppercase tracking-wide">
                  {{ $p->category }}
                </span>
                <div class="mt-1 h-12 font-semibold text-gray-800 leading-snug line-clamp-2">
                  {{ $p->name }}
                </div>
                <div class="mt-1 text-lg font-bold text-blue-700">L {{ number_format($p->price,2) }}</div>
              </div>
            </button>
          @endforeach
        </div>
      </div>
    </div>

    {{-- MODAL: Nuevo cliente --}}
    <template x-teleport="body">
      <div
        x-show="openNewClient"
        x-cloak
        class="fixed inset-0 z-[1000] grid place-items-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition.opacity
        @click.self.stop.prevent="openNewClient=false"
        @keydown.escape.stop.prevent="openNewClient=false"
        aria-modal="true" role="dialog" tabindex="-1"
      >
        <div
          class="w-[min(96vw,36rem)] max-h-[90vh] overflow-auto bg-white rounded-2xl shadow-2xl ring-1 ring-black/5"
          x-transition.scale
          @click.outside.stop.prevent="openNewClient=false"
        >
          <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Nuevo cliente</h3>
            <button
              type="button"
              class="px-2.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50"
              @click.stop.prevent="openNewClient=false"
              aria-label="Cerrar"
            >✕</button>
          </div>

          <div class="p-6 space-y-4">
            <input
              x-ref="cliname"
              x-model="newClient.name"
              type="text"
              placeholder="Nombre *"
              class="w-full rounded-xl border border-gray-300 px-3 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <input
              x-model="newClient.phone"
              type="text"
              placeholder="Teléfono (opcional)"
              class="w-full rounded-xl border border-gray-300 px-3 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
              inputmode="tel"
            >
          </div>

          <div class="p-6 pt-0 flex justify-end gap-2">
            <button
              type="button"
              class="px-4 py-2 rounded-xl border border-gray-300 hover:bg-gray-50"
              @click.stop.prevent="openNewClient=false"
            >
              Cancelar
            </button>

            <button
              type="button"
              class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
              @click="createClient()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </template>

    {{-- MODAL: Efectivo / Cambio --}}
    <template x-teleport="body">
      <div
        x-show="openCash"
        x-cloak
        class="fixed inset-0 z-[1100] grid place-items-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm"
        x-transition.opacity
        @click.self="closeCash()"
        @keydown.escape.window.prevent="closeCash()"
        aria-modal="true" role="dialog" tabindex="-1"
      >
        <div
          class="w-[min(96vw,38rem)] max-h-[92vh] bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 flex flex-col"
          x-transition.scale
        >
          <div class="p-5 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Cobro en efectivo</h3>
            <button type="button"
              class="inline-flex items-center justify-center w-9 h-9 rounded-xl border hover:bg-gray-50"
              @click="closeCash()" aria-label="Cerrar">✕</button>
          </div>

          <div class="p-5 space-y-5 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="rounded-2xl bg-blue-50 p-4">
                <div class="text-xs font-medium text-blue-800/70 uppercase tracking-wide">Total a pagar</div>
                <div class="mt-1 text-4xl font-black text-blue-700" x-text="money(grandTotal())"></div>
              </div>
              <div class="rounded-2xl bg-emerald-50 p-4">
                <div class="text-xs font-medium text-emerald-900/70 uppercase tracking-wide">Cambio</div>
                <div class="mt-1 text-4xl font-black"
                     :class="cashChange() >= 0 ? 'text-emerald-700' : 'text-red-600'">
                  <span x-text="cashChange()>=0 ? money(cashChange()) : 'Faltan ' + money(Math.abs(cashChange()))"></span>
                </div>
              </div>
            </div>

            <div>
              <label class="text-sm font-semibold text-gray-700">Dinero recibido</label>
              <div class="mt-2 flex gap-2">
                <input
                  x-ref="cashInput"
                  x-model.number="cashGiven"
                  type="number" inputmode="decimal" min="0" step="1"
                  inputmode="numeric"
                  class="appearance-none flex-1 rounded-2xl border border-gray-300 px-4 py-3 text-2xl text-right
                         focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="0.00"
                   @wheel.prevent>
                  
                <button type="button"
                  class="px-4 py-3 rounded-2xl border bg-white hover:bg-gray-50 font-semibold"
                  @click="cashGiven = grandTotal()">Exacto</button>
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-600 mb-2">Atajos</div>
              <div class="grid grid-cols-3 sm:grid-cols-3 gap-2">
                <template x-for="v in cashQuick" :key="v">
                  <button type="button"
                    class="py-3 rounded-2xl border bg-white hover:bg-gray-50 font-medium"
                    @click="cashGiven = +(Number(cashGiven||0) + Number(v)).toFixed(2)">
                    + <span x-text="money(v)"></span>
                  </button>
                </template>
                <button type="button"
                  class="col-span-3 py-3 rounded-2xl border bg-white hover:bg-gray-50 font-medium"
                  @click="cashGiven = roundToBill(grandTotal())">
                  Redondear a billete
                </button>
                <button type="button"
                  class="col-span-3 py-3 rounded-2xl border bg-white hover:bg-gray-50 font-medium"
                  @click="cashGiven = 0">
                  Borrar
                </button>
              </div>
            </div>
          </div>

          <div class="p-5 border-t bg-white flex flex-col sm:flex-row gap-2 sm:justify-end">
            <button type="button"
              class="px-4 py-3 rounded-2xl border hover:bg-gray-50 font-semibold"
              @click="closeCash()">Cancelar</button>
            <button type="button"
              class="px-5 py-3 rounded-2xl text-white font-semibold shadow-lg
                     disabled:opacity-60 disabled:cursor-not-allowed
                     bg-blue-600 hover:bg-blue-700"
              :disabled="cashChange()<0"
              @click="confirmCash()">
              Confirmar y registrar
            </button>
          </div>
        </div>
      </div>
    </template>

    {{-- Modal Cobrar Abono (CxC) --}}
    <template x-teleport="body">
      <div x-data="abonoCxC()" x-cloak
           x-on:abono-open.window="openModal()"
           x-on:abono-close.window="closeModal()"
           @keydown.escape.window.prevent="closeModal()">

        <div x-show="open"
             x-transition.opacity
             class="fixed inset-0 z-[1200] flex items-center justify-center p-4"
             style="display:none">
          <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>

          <div class="relative w-full max-w-xl rounded-2xl bg-white p-4 space-y-4 shadow-xl"
               x-transition.scale
               @click.outside="closeModal()">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold">Cobrar abono a cliente</h3>
              <button @click="closeModal()" class="text-gray-500 hover:text-black">✕</button>
            </div>

            <div>
              <label class="block text-sm font-medium">Buscar cliente</label>
              <input type="text" x-ref="buscar" x-model="q" @input.debounce.300ms="buscar()"
                     class="mt-1 w-full rounded-xl border p-2" placeholder="Nombre o teléfono">
              <div class="mt-2 max-h-40 overflow-y-auto border rounded-xl" x-show="sugerencias.length">
                <template x-for="c in sugerencias" :key="c.id">
                  <button @click="seleccionar(c)"
                          class="w-full text-left px-3 py-2 hover:bg-gray-100">
                    <span class="font-semibold" x-text="c.name"></span>
                    <span class="text-sm text-gray-500" x-text="c.phone ? ' · '+c.phone : ''"></span>
                  </button>
                </template>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <div>
                <label class="block text-sm font-medium">Nombre</label>
                <input type="text" x-model="nuevo.name" class="mt-1 w-full rounded-xl border p-2" placeholder="Ej. Juan Pérez">
              </div>
              <div>
                <label class="block text-sm font-medium">Teléfono (opcional)</label>
                <input type="text" x-model="nuevo.phone" class="mt-1 w-full rounded-xl border p-2" placeholder="Ej. 9999-9999">
              </div>
              <div class="sm:col-span-2">
                <button @click="crearCliente()"
                        class="mt-2 px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold">
                  Crear y seleccionar
                </button>
              </div>
            </div>

            <template x-if="cliente">
              <div class="p-3 rounded-xl bg-gray-50 border">
                <div class="font-semibold">Cliente seleccionado:</div>
                <div x-text="cliente.name"></div>
                <div class="text-sm text-gray-600" x-text="cliente.phone"></div>
              </div>
            </template>

            <template x-if="saldo !== null">
              <div class="p-3 rounded-xl bg-white border">
                <div class="flex justify-between text-sm">
                  <span>Total a crédito</span>
                  <span>L <span x-text="Number(saldoInfo.credit_total).toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between text-sm">
                  <span>Total abonado</span>
                  <span>L <span x-text="Number(saldoInfo.payments_total).toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between font-semibold">
                  <span>Saldo actual</span>
                  <span>L <span x-text="Number(saldo).toFixed(2)"></span></span>
                </div>
              </div>
            </template>

            <template x-if="historial.length">
              <div class="p-3 rounded-xl bg-white border">
                <div class="font-semibold mb-2">Últimos abonos</div>
                <ul class="space-y-1 max-h-32 overflow-y-auto">
                  <template x-for="p in historial" :key="p.id">
                    <li class="text-sm flex justify-between">
                      <span x-text="new Date(p.created_at).toLocaleString()"></span>
                      <span>
                        <span x-text="p.method.toUpperCase()"></span>
                        · L <span x-text="Number(p.amount).toFixed(2)"></span>
                      </span>
                    </li>
                  </template>
                </ul>
              </div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium">Monto</label>
                <input type="number" step="0.01" min="0.01" x-model.number="form.amount"
                       class="mt-1 w-full rounded-xl border p-2" placeholder="0.00">
              </div>
              <div>
                <label class="block text-sm font-medium">Método</label>
                <select x-model="form.method" class="mt-1 w-full rounded-xl border p-2">
                  <option value="efectivo">Efectivo</option>
                  <option value="tarjeta">Tarjeta</option>
                  <option value="transferencia">Transferencia</option>
                </select>
              </div>
              <div class="sm:col-span-3">
                <label class="block text-sm font-medium">Notas (opcional)</label>
                <input type="text" x-model="form.notes" class="mt-1 w-full rounded-xl border p-2"
                       placeholder="Comentario breve">
              </div>
            </div>

            <div class="flex items-center justify-end gap-2">
              <button @click="closeModal()" class="px-4 py-2 rounded-xl border">Cancelar</button>
              <button @click="cobrarAbono()" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold">
                Registrar abono
              </button>
            </div>

            <p class="text-sm" :class="msgClass" x-text="msg" x-show="msg"></p>
          </div>
        </div>
      </div>
    </template>

  </div>

  <script>
  window.abonoCxC = function () {
    return {
      open: false,
      q: '',
      sugerencias: [],
      cliente: null,
      nuevo: { name: '', phone: '' },
      historial: [],
      saldo: null,
      saldoInfo: { credit_total: 0, payments_total: 0 },
      form: { amount: '', method: 'efectivo', notes: '' },
      msg: '', msgClass: 'text-gray-600',

      openModal() {
        this.reset();
        this.open = true;
        document.body.style.overflow = 'hidden';
        this.$nextTick(() => this.$refs?.buscar?.focus());
      },
      closeModal() {
        this.open = false;
        this.reset();
        document.body.style.overflow = '';
      },
      reset() {
        this.q = '';
        this.sugerencias = [];
        this.cliente = null;
        this.nuevo = { name: '', phone: '' };
        this.form  = { amount: '', method: 'efectivo', notes: '' };
        this.historial = [];
        this.saldo = null;
        this.saldoInfo = { credit_total: 0, payments_total: 0 };
        this.msg = ''; this.msgClass = 'text-gray-600';
      },
      flash(text, cls = 'text-gray-600') {
        this.msg = text; this.msgClass = cls;
        setTimeout(() => { this.msg = ''; }, 2500);
      },

      async buscar() {
        if (!this.q?.trim()) { this.sugerencias = []; return; }
        try {
          const r = await fetch(`/caja/clientes?q=${encodeURIComponent(this.q)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          this.sugerencias = await r.json();
        } catch { this.sugerencias = []; }
      },
      seleccionar(c) {
        this.cliente = c;
        this.q = c.name;
        this.sugerencias = [];
        this.cargarHistorial();
        this.cargarSaldo();
      },
      async crearCliente() {
        if (!this.nuevo.name?.trim() && this.q?.trim()) this.nuevo.name = this.q.trim();
        if (!this.nuevo.name?.trim()) { this.flash('Debes escribir el nombre.','text-red-600'); return; }
        try {
          const r = await fetch('/caja/clientes', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ name: this.nuevo.name, phone: this.nuevo.phone || null }),
          });
          const cli = await r.json();
          if (r.ok && cli?.id) {
            this.cliente = cli; this.q = cli.name; this.sugerencias = [];
            this.cargarHistorial(); this.cargarSaldo();
            this.nuevo = { name: '', phone: '' };
            this.flash('Cliente creado y seleccionado.','text-emerald-600');
          } else {
            this.flash(cli?.message || 'No se pudo crear el cliente.','text-red-600');
          }
        } catch { this.flash('Error de red al crear cliente.','text-red-600'); }
      },
      async cargarHistorial() {
        if (!this.cliente?.id) { this.historial = []; return; }
        try {
          const r = await fetch(`/caja/clientes/${this.cliente.id}/abonos?limit=5`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          const data = await r.json();
          this.historial = data?.payments ?? [];
        } catch { this.historial = []; }
      },
      async cargarSaldo() {
        this.saldo = null;
        this.saldoInfo = { credit_total: 0, payments_total: 0 };
        if (!this.cliente?.id) return;
        try {
          const r = await fetch(`/caja/clientes/${this.cliente.id}/saldo`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          const data = await r.json();
          this.saldo = data?.balance ?? 0;
          this.saldoInfo = {
            credit_total: data?.credit_total ?? 0,
            payments_total: data?.payments_total ?? 0,
          };
        } catch {}
      },
      async cobrarAbono() {
        if (!this.cliente?.id) { this.flash('Selecciona o crea un cliente.','text-red-600'); return; }
        if (!this.form.amount || Number(this.form.amount) <= 0) { this.flash('Monto inválido.','text-red-600'); return; }
        try {
          const r = await fetch(`/caja/clientes/${this.cliente.id}/abono`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(this.form),
          });
          const data = await r.json();
          if (data?.ok) {
            this.flash('Abono registrado.','text-emerald-700');
            this.cargarHistorial(); this.cargarSaldo();
            window.dispatchEvent(new CustomEvent('pos-refresh-summary'));
            setTimeout(() => this.closeModal(), 800);
          } else {
            this.flash(data?.message || 'No se pudo registrar el abono.','text-red-600');
          }
        } catch { this.flash('Error de red al registrar el abono.','text-red-600'); }
      },
    };
  };
  </script>

  <script>
    function pos(){
      return {
        hasShift: false,

        async refreshShift(){
          try{
            const r = await fetch('{{ route('caja.shift.current') }}', {
              headers:{
                'Accept':'application/json',
                'X-Requested-With':'XMLHttpRequest',
                'Cache-Control':'no-store'
              },
              credentials:'same-origin',
              cache:'no-store'
            });
            const j = await r.json().catch(()=>({shift:null}));
            this.hasShift = !!j.shift;
          }catch{
            this.hasShift = false;
          }
        },

        activeCat: null,
        search: '',
        cart: [],
        payment: 'cash',
        feePct: 0,

        client: null,
        clientQuery: '',
        clientResults: [],
        dueDate: new Date(Date.now()+30*24*3600*1000).toISOString().slice(0,10),
        openNewClient:false,
        newClient:{name:'', phone:''},

        openCash: false,
        cashGiven: 0,
        cashQuick: [10,20,50,100,200,500],
        async openCashModal(){
  // ✅ VALIDAR PRODUCTOS VENCIDOS ANTES DE ABRIR EL MODAL
  const expiredCheck = await this.checkExpiredProducts();
  if (!expiredCheck.success) {
    Swal.fire({
      icon: 'error',
      title: '⚠️ Producto Vencido',
      html: `
        <div style="text-align: left; padding: 10px;">
          <p style="font-size: 16px; margin-bottom: 10px;">
            <strong>El siguiente producto está vencido:</strong>
          </p>
          <div style="background: #fee; border-left: 4px solid #f00; padding: 15px; border-radius: 5px;">
            <p style="font-size: 18px; font-weight: bold; color: #d00; margin-bottom: 5px;">
              📦 ${expiredCheck.productName}
            </p>
            <p style="font-size: 14px; color: #666;">
              🗓️ Fecha de vencimiento: <strong>${expiredCheck.expiryDate}</strong>
            </p>
          </div>
          <p style="font-size: 14px; color: #666; margin-top: 15px;">
            ❌ <strong>No se puede vender este producto.</strong> Por favor, retíralo del carrito.
          </p>
        </div>
      `,
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#dc2626',
      width: '500px'
    });
    return; // NO ABRIR EL MODAL
  }

  // Si todo está bien, abrir el modal normalmente
  this.cashGiven = this.grandTotal();
  this.openCash = true;
  this.$nextTick(()=> this.$refs.cashInput?.focus());
},
        closeCash(){ this.openCash = false; },
        cashChange(){ return +(Number(this.cashGiven||0) - this.grandTotal()).toFixed(2); },
        roundToBill(total){
          const bills = [10,20,50,100,200,500,1000];
          for(const b of bills){ if(total <= b) return b; }
          return Math.ceil(total/100)*100;
        },
        async confirmCash(){
          if (this.cashChange() < 0) return;
          await this.pay({
            cash_received: Number(this.cashGiven||0),
            cash_change:   this.cashChange()
          });
          this.openCash=false;
        },

        scanBuf: '',
        scanTimer: null,
        _clientTimer: null,

        init(){
          window.addEventListener('keydown', this.catchScan.bind(this));
          this.$watch('openNewClient', open => {
            document.body.style.overflow = open ? 'hidden' : '';
          });
          
          this.refreshShift();
          window.addEventListener('focus', () => this.refreshShift());
          
          this.loadPendingSalesCount();
          window.posInstance = this;
        },

        setCat(id){ this.activeCat = id },
        showProduct(cat, name){
          if(this.activeCat && this.activeCat !== cat) return false;
          if(this.search?.trim().length)
            return name.toLowerCase().includes(this.search.toLowerCase());
          return true;
        },

        add(p){
          const i = this.cart.findIndex(x=>x.id===p.id);
          if(i>-1){ this.cart[i].qty++; }
          else { this.cart.push({...p, qty:1}); }
          this.toast(`+1 ${p.name}`);
        },
        inc(i){ this.cart[i].qty++; },
        dec(i){ if(--this.cart[i].qty<=0) this.cart.splice(i,1); },
        remove(i){ this.cart.splice(i,1); },
        clear(){ this.cart=[]; },

        itemsCount(){ return this.cart.reduce((a,b)=>a+Number(b.qty),0); },
        subtotal(){ return this.cart.reduce((a,b)=>a+b.qty*b.price,0); },
        feeAmount(){
          if(this.payment!=='card') return 0;
          return +(this.subtotal() * (Number(this.feePct||0)/100)).toFixed(2);
        },
        grandTotal(){ return +(this.subtotal() + this.feeAmount()).toFixed(2); },
        money(n){ return 'L ' + Number(n).toFixed(2); },

        payBtn(m){
          const active = {
            cash:     'bg-emerald-600 text-white shadow-lg',
            card:     'bg-indigo-600 text-white shadow-lg',
            transfer: 'bg-amber-500 text-white shadow-lg',
            credit:   'bg-sky-600 text-white shadow-lg',
          };
          const base = 'bg-gray-100 text-gray-800 border border-gray-300 hover:bg-gray-200';
          return (this.payment===m) ? active[m] : base;
        },
        toast(msg){
          const id = 'toast-'+Date.now();
          document.body.insertAdjacentHTML('beforeend',
            `<div id="${id}" class="fixed bottom-5 left-1/2 -translate-x-1/2 bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-semibold">${msg}</div>`);
          setTimeout(()=>document.getElementById(id)?.remove(), 900);
        },

        async searchClients(){
          if(this._clientTimer) clearTimeout(this._clientTimer);
          this._clientTimer = setTimeout(async ()=>{
            if(!this.clientQuery){ this.clientResults=[]; return; }
            try{
              const res = await fetch(`{{ route('caja.clients') }}?q=${encodeURIComponent(this.clientQuery)}`);
              this.clientResults = res.ok ? await res.json() : [];
            }catch{ this.clientResults=[]; }
          }, 250);
        },

        async createClient(){
          if(!this.newClient.name?.trim()){
            alert('Escribe el nombre del cliente.');
            return;
          }
          try{
            const r = await fetch(`{{ route('caja.clients.store') }}`,{
              method:'POST',
              headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
              },
              body: JSON.stringify(this.newClient)
            });

            if(!r.ok){
              const t = await r.text();
              alert(t || 'No se pudo crear el cliente.');
              return;
            }

            const c = await r.json();
            this.client = c;
            this.newClient = {name:'', phone:''};
            this.openNewClient = false;
            this.toast('Cliente guardado ✅');

          }catch{
            alert('Error de red.');
          }
        },

        catchScan(e){
          if (this.openNewClient || this.openCash) return;
          const tag = document.activeElement?.tagName;
          if (tag==='INPUT' || tag==='TEXTAREA') return;
          const ch = e.key;
          if(ch === 'Enter'){
            const code = this.scanBuf.trim(); this.scanBuf='';
            if(code.length) this.lookupBarcode(code);
            return;
          }
          if(this.scanTimer) clearTimeout(this.scanTimer);
          this.scanBuf += ch;
          this.scanTimer = setTimeout(()=> this.scanBuf='', 120);
        },
        async lookupBarcode(code){
          try{
            const res = await fetch(`{{ route('caja.barcode', ['code'=>'__CODE__']) }}`.replace('__CODE__', encodeURIComponent(code)));
            if(!res.ok) return;
            const p = await res.json();
            this.add({id:p.id, name:p.name, price:p.price, cat:p.cat});
          }catch(_){}
        },

        async pay(extras = {}) {
          const cr = await fetch('{{ route('caja.shift.current') }}', {
            headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','Cache-Control':'no-store'}
          });
          const cj = await cr.json().catch(()=>({shift:null}));
          if (!cj.shift) { alert('Debes abrir un turno antes de vender.'); return; }

          if(!this.cart.length) return;


// ✅ VALIDAR PRODUCTOS VENCIDOS ANTES DE CONTINUAR
  const expiredCheck = await this.checkExpiredProducts();
  if (!expiredCheck.success) {
    Swal.fire({
      icon: 'error',
      title: '⚠️ Producto Vencido',
      html: `
        <div style="text-align: left; padding: 10px;">
          <p style="font-size: 16px; margin-bottom: 10px;">
            <strong>El siguiente producto está vencido:</strong>
          </p>
          <div style="background: #fee; border-left: 4px solid #f00; padding: 15px; border-radius: 5px;">
            <p style="font-size: 18px; font-weight: bold; color: #d00; margin-bottom: 5px;">
              📦 ${expiredCheck.productName}
            </p>
            <p style="font-size: 14px; color: #666;">
              🗓️ Fecha de vencimiento: <strong>${expiredCheck.expiryDate}</strong>
            </p>
          </div>
          <p style="font-size: 14px; color: #666; margin-top: 15px;">
            ❌ <strong>No se puede vender este producto.</strong> Por favor, retíralo del carrito.
          </p>
        </div>
      `,
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#dc2626',
      width: '500px'
    });
    return; // Detener el proceso
  }


          if(this.payment==='credit' && !this.client){
            alert('Selecciona o crea un cliente para ventas a crédito.');
            return;
          }

          const body = {
            items: this.cart.map(x=>({id:x.id,qty:x.qty,price:x.price})),
            payment:  this.payment,
            fee_pct:  (this.payment==='card') ? Number(this.feePct||0) : 0,
            surcharge: this.feeAmount(),
            client_id: (this.payment==='credit' && this.client) ? this.client.id : null,
            due_date:  (this.payment==='credit') ? this.dueDate : null,
            ...extras,
          };

          try{
            const r = await fetch(`{{ route('caja.charge') }}`,{
              method:'POST',
              headers:{
                'Content-Type':'application/json',
                'Accept':'application/json',
                'X-Requested-With':'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Cache-Control':'no-store'
              },
              credentials: 'same-origin',
              cache: 'no-store',
              body: JSON.stringify(body)
            });

          if (!r.ok) {
  let msg = 'No se pudo registrar la venta.';
  try {
    const err = await r.json();
    if (err?.message) {
      msg = err.message;
      
      // ✅ Detectar si es un error de producto vencido
      if (msg.includes('PRODUCTO_VENCIDO')) {
        const parts = msg.split('|');
        const productName = parts[1] || 'Producto';
        const expiryDate = parts[2] || 'Fecha desconocida';
        
        Swal.fire({
          icon: 'error',
          title: '⚠️ Producto Vencido',
          html: `
            <div style="text-align: left; padding: 10px;">
              <p style="font-size: 16px; margin-bottom: 10px;">
                <strong>El siguiente producto está vencido:</strong>
              </p>
              <div style="background: #fee; border-left: 4px solid #f00; padding: 15px; border-radius: 5px;">
                <p style="font-size: 18px; font-weight: bold; color: #d00; margin-bottom: 5px;">
                  📦 ${productName}
                </p>
                <p style="font-size: 14px; color: #666;">
                  🗓️ Fecha de vencimiento: <strong>${expiryDate}</strong>
                </p>
              </div>
              <p style="font-size: 14px; color: #666; margin-top: 15px;">
                ❌ <strong>No se puede vender este producto.</strong> Por favor, retíralo del carrito.
              </p>
            </div>
          `,
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#dc2626',
          width: '500px'
        });
        return;
      }
    }
  } catch {}
  
  // Error genérico
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: msg,
    confirmButtonColor: '#dc2626'
  });
  return;
}

            window.dispatchEvent(new CustomEvent('sale:registered', {
              detail: { total: this.grandTotal(), payment: this.payment }
            }));

            this.clear();
            this.feePct = 0;
            this.payment = 'cash';
            this.client = null;
            this.clientQuery = '';
            this.clientResults = [];
            this.toast('Venta registrada ✅');

          }catch(e){
            console.error(e);
            alert('Error de red.');
          }
        },

        holdSale(){
          if(!this.cart.length){
            alert('⚠️ No hay productos en el carrito para poner en espera');
            return;
          }
          document.getElementById('holdSaleModal').classList.remove('hidden');
        },

        async showPendingSales(){
          try{
            const r = await fetch('/sales-management/pending',{
              headers:{
                'Accept':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              }
            });
            const data = await r.json();
            
            const list = document.getElementById('pendingSalesList');
            const noSales = document.getElementById('noPendingSales');
            
            if(data.pending_sales.length === 0){
              list.innerHTML = '';
              noSales.classList.remove('hidden');
            }else{
              noSales.classList.add('hidden');
              list.innerHTML = '';
              
              data.pending_sales.forEach(sale => {
                const time = new Date(sale.created_at).toLocaleTimeString('es-HN',{
                  hour:'2-digit', minute:'2-digit'
                });
                
                const div = document.createElement('div');
                div.className = 'border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition';
                div.innerHTML = `
                  <div class="flex justify-between items-center">
                    <div class="flex-1">
                      <div class="font-semibold text-lg">${sale.customer_name || 'Sin nombre'}</div>
                      <div class="text-sm text-gray-600">🕐 ${time} • ${sale.items.length} productos</div>
                      <div class="text-lg font-bold text-blue-600 mt-1">L. ${parseFloat(sale.total).toFixed(2)}</div>
                      ${sale.notes ? `<div class="text-xs text-gray-500 mt-1">📝 ${sale.notes}</div>` : ''}
                    </div>
                    <div class="flex flex-col gap-2 ml-4">
                      <button onclick="window.posInstance.retrievePendingSale(${sale.id})"
                              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-sm">
                        ▶️ Continuar
                      </button>
                      <button onclick="window.posInstance.deletePendingSale(${sale.id})"
                              class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg text-sm">
                        🗑️ Eliminar
                      </button>
                    </div>
                  </div>
                `;
                list.appendChild(div);
              });
            }
            
            document.getElementById('pendingSalesModal').classList.remove('hidden');
          }catch(e){
            console.error(e);
            alert('❌ Error al cargar ventas en espera');
          }
        },

async retrievePendingSale(id){
  try{
    const r = await fetch(`/sales-management/pending/${id}`,{
      headers:{
        'Accept':'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });
    const data = await r.json();

    this.cart = data.pending_sale.items.map(item => ({
      id: item.id,
      name: item.name || 'Producto',
      price: parseFloat(item.price),
      qty: parseFloat(item.qty),
      cat: item.category || ''
    }));

    document.getElementById('pendingSalesModal').classList.add('hidden');
    this.toast('✅ Venta recuperada');

    // <- NUEVO: bajar el contador porque ya la "sacamos"
    this.loadPendingSalesCount();

  }catch(e){
    console.error(e);
    alert('❌ Error al recuperar venta');
  }
}
,

        async deletePendingSale(id){
          if(!confirm('¿Eliminar esta venta en espera?')) return;
          
          try{
            const r = await fetch(`/sales-management/pending/${id}`,{
              method:'DELETE',
              headers:{
                'Accept':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              }
            });
            
            const data = await r.json();
            if(data.ok){
              this.toast('✅ Venta eliminada');
              this.showPendingSales();
              this.loadPendingSalesCount();
            }
          }catch(e){
            console.error(e);
            alert('❌ Error al eliminar');
          }
        },

        async loadPendingSalesCount(){
          try{
            const r = await fetch('/sales-management/pending',{
              headers:{
                'Accept':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              }
            });
            const data = await r.json();
            const countEl = document.getElementById('pending-count');
            if(countEl) countEl.textContent = data.pending_sales.length;
          }catch(e){
            console.error(e);
          }
        },

        openReturnModal(){
          document.getElementById('returnModal').classList.remove('hidden');
          document.getElementById('returnStep1').classList.remove('hidden');
          document.getElementById('returnStep2').classList.add('hidden');
          document.getElementById('returnStep3').classList.add('hidden');
          document.getElementById('returnError').classList.add('hidden');
        },

        async lookupProduct(search){
          try{
            const r = await fetch(`/caja/barcode/${encodeURIComponent(search)}`);
            if(!r.ok) return null;
            return await r.json();
          }catch{
            return null;
          }
        }



,

// ✅ FUNCIÓN PARA VALIDAR PRODUCTOS VENCIDOS
async checkExpiredProducts() {
  for (const item of this.cart) {
    try {
      const response = await fetch(`/api/products/${item.id}/check-expiry`);
      const data = await response.json();
      
      if (data.expired) {
        return {
          success: false,
          productName: data.name,
          expiryDate: data.expires_at
        };
      }
    } catch (error) {
      console.error('Error checking product expiry:', error);
    }
  }
  
  return { success: true };
}



      };
    }
  </script>

  @push('scripts')
  <script>
  let selectedSaleItemForReturn = null;
  let selectedProductForReturn = null;

  document.addEventListener('DOMContentLoaded', function() {
      if(window.posInstance) {
          window.posInstance.loadPendingSalesCount();
      }
  });

  async function confirmHoldSale() {
      const customerName = document.getElementById('holdCustomerName').value;
      const notes = document.getElementById('holdNotes').value;
      
      const posApp = window.posInstance;
      if(!posApp || !posApp.cart || posApp.cart.length === 0){
          alert('⚠️ No hay productos en el carrito');
          return;
      }
      
      try {
          const response = await fetch('/sales-management/hold', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({
                  items: posApp.cart.map(item => ({
                      id: item.id,
                      qty: item.qty,
                      price: item.price
                  })),
                  customer_name: customerName,
                  notes: notes
              })
          });
          
          const data = await response.json();
          
          if (data.ok) {
              alert('✅ Venta guardada en espera');
              posApp.clear();
              closeHoldModal();
              posApp.loadPendingSalesCount();
          } else {
              alert('❌ Error: ' + data.message);
          }
      } catch (error) {
          console.error('Error:', error);
          alert('❌ Error al guardar venta en espera');
      }
  }

  function closeHoldModal() {
      document.getElementById('holdSaleModal').classList.add('hidden');
      document.getElementById('holdCustomerName').value = '';
      document.getElementById('holdNotes').value = '';
  }

  function closePendingSalesModal() {
      document.getElementById('pendingSalesModal').classList.add('hidden');
  }

 async function searchProductForReturn() {
  const search = document.getElementById('returnProductSearch').value.trim();
  const suggestBox = document.getElementById('returnProductSuggestions');

  if (!search) {
    alert('⚠️ Escribe algo para buscar');
    return;
  }

  // 1) Intentar sugerencias (nombre o barcode parcial)
  try {
    const r = await fetch(`/sales-management/products/suggest?q=${encodeURIComponent(search)}`, {
      headers: { 'Accept':'application/json' }
    });
    const items = r.ok ? await r.json() : [];

    // Si hay UNA coincidencia clara por nombre exacto o barcode igual, úsala
    const exactByName = items.find(p => p.name.toLowerCase() === search.toLowerCase());
    const exactByCode = items.find(p => (p.barcode || '').toLowerCase() === search.toLowerCase());

    const chosen = exactByName || exactByCode || null;

    if (chosen) {
      return selectProductForReturn(chosen);
    }

    // Si hay varias, muestra la lista y que el cajero elija
    if (items.length > 0) {
      suggestBox.innerHTML = items.map(p => `
        <button class="w-full text-left px-3 py-2 hover:bg-blue-50 flex items-center gap-2"
                onclick='selectProductForReturn(${JSON.stringify(p)})'>
          <span class="font-medium">${p.name}</span>
          <span class="text-xs text-gray-500 ml-2">${p.barcode ?? ''}</span>
          <span class="text-xs text-gray-500 ml-auto">${p.category ?? ''}</span>
        </button>
      `).join('');
      suggestBox.classList.remove('hidden');
      return;
    }
  } catch {}

  // 2) Fallback: intentar como barcode exacto (por si se escaneó)
  const posApp = window.posInstance;
  const product = await posApp.lookupProduct(search);

  if (!product) {
    showReturnError('Producto no encontrado en el catálogo');
    return;
  }

  // Si llegó aquí es porque el endpoint de barcode respondió
  selectProductForReturn(product);
}


 function displaySalesForReturn(sales) {
  const list = document.getElementById('returnSalesList');
  list.innerHTML = '';
  
  sales.forEach(sale => {
    const time = new Date(sale.created_at).toLocaleTimeString('es-HN', {
      hour: '2-digit',
      minute: '2-digit'
    });
    
    // ============================================
    // ✅ DETECTAR SI ES DEVOLUCIÓN (cantidad negativa)
    // ============================================
    const cantidad = parseFloat(sale.qty);
    const esDevolucion = cantidad < 0;
    const cantidadAbsoluta = Math.abs(cantidad);
    
    const div = document.createElement('div');
    // Si es devolución, agregar borde rojo y fondo diferente
    const estiloExtra = esDevolucion 
      ? 'border-red-300 bg-red-50' 
      : 'border-gray-200 hover:bg-gray-50';
    
    div.className = `border rounded-lg p-3 transition cursor-pointer ${estiloExtra}`;
    
    // ============================================
    // ✅ MOSTRAR DIFERENTE SI YA FUE DEVUELTO
    // ============================================
    if (esDevolucion) {
      // Es una devolución - mostrar con texto especial
      div.innerHTML = `
        <div class="flex justify-between items-center">
          <div class="flex-1">
            <div class="font-semibold text-red-700">🕐 ${time} - Venta #${sale.sale_id}</div>
            <div class="text-sm text-red-600">
              ${cantidadAbsoluta}x ${sale.product_name} - L. ${parseFloat(sale.total).toFixed(2)}
            </div>
            <div class="text-xs text-red-500 mt-1">
              ⚠️ Esta es una devolución registrada
            </div>
          </div>
          <div class="px-4 py-2 bg-red-200 text-red-800 font-bold rounded-lg cursor-not-allowed">
            ✅ YA DEVUELTO
          </div>
        </div>
      `;
    } else {
      // Es una venta normal - mostrar con botón DEVOLVER
      div.innerHTML = `
        <div class="flex justify-between items-center">
          <div class="flex-1">
            <div class="font-semibold">🕐 ${time} - Venta #${sale.sale_id}</div>
            <div class="text-sm text-gray-600">
              ${sale.qty}x ${sale.product_name} - L. ${parseFloat(sale.total).toFixed(2)}
            </div>
          </div>
          <button onclick='selectSaleForReturn(${JSON.stringify(sale)})'
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
            DEVOLVER
          </button>
        </div>
      `;
    }
    
    list.appendChild(div);
  });
  
  document.getElementById('returnStep2').classList.remove('hidden');
}
  function selectSaleForReturn(sale) {
      selectedSaleItemForReturn = sale;
      
      document.getElementById('selectedSaleInfo').textContent = 
          `Venta #${sale.sale_id} • ${sale.qty}x ${sale.product_name} • L. ${parseFloat(sale.total).toFixed(2)}`;
      
      document.getElementById('returnMaxQty').textContent = sale.qty;
      document.getElementById('returnQty').max = sale.qty;
      document.getElementById('returnQty').value = sale.qty;
      
      document.getElementById('returnStep3').classList.remove('hidden');
      document.getElementById('returnStep3').scrollIntoView({ behavior: 'smooth' });
  }

  function updateConditionWarning() {
      const isDamaged = document.getElementById('conditionDamaged').checked;
      const warning = document.getElementById('conditionWarning');
      
      if (isDamaged) {
          warning.classList.remove('hidden');
      } else {
          warning.classList.add('hidden');
      }
  }

  async function processReturn() {
      const qty = parseFloat(document.getElementById('returnQty').value);
      const reason = document.getElementById('returnReason').value;
      const condition = document.querySelector('input[name="productCondition"]:checked').value;
      
      if (!qty || qty <= 0) {
          alert('⚠️ Ingresa una cantidad válida');
          return;
      }
      
      if (qty > selectedSaleItemForReturn.qty) {
          alert(`⚠️ No puedes devolver más de ${selectedSaleItemForReturn.qty}`);
          return;
      }
      
      if (!reason) {
          alert('⚠️ Selecciona una razón');
          return;
      }
      
      const conditionText = condition === 'good' 
          ? 'BUEN ESTADO (regresa al inventario)' 
          : 'MAL ESTADO (NO regresa - MERMA)';
      const total = qty * selectedSaleItemForReturn.price;
      
      const confirmMsg = 
          `¿Confirmar devolución?\n\n` +
          `Cantidad: ${qty}\n` +
          `Total a devolver: L. ${total.toFixed(2)}\n` +
          `Estado: ${conditionText}\n\n` +
          `Esta acción no se puede deshacer.`;
      
      if (!confirm(confirmMsg)) return;
      
      try {
          const response = await fetch('/sales-management/return-item', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({
                  sale_id: selectedSaleItemForReturn.sale_id,
                  sale_item_id: selectedSaleItemForReturn.sale_item_id,
                  qty: qty,
                  return_reason: reason,
                  product_condition: condition
              })
          });
          
          const data = await response.json();
          
          if (data.ok) {
              alert(
                  data.message + 
                  '\n\nMonto devuelto: L. ' + data.amount_returned.toFixed(2)
              );
              
              closeReturnModal();
          } else {
              alert('❌ Error: ' + data.message);
          }
          
      } catch (error) {
          console.error('Error:', error);
          alert('❌ Error al procesar devolución');
      }
  }

  function showReturnError(message) {
      document.getElementById('returnErrorMessage').textContent = message;
      document.getElementById('returnError').classList.remove('hidden');
  }

  function cancelReturn() {
      if (confirm('¿Cancelar devolución?')) {
          closeReturnModal();
      }
  }

  function closeReturnModal() {
      document.getElementById('returnModal').classList.add('hidden');
      selectedSaleItemForReturn = null;
      selectedProductForReturn = null;
      
      document.getElementById('returnProductSearch').value = '';
      document.getElementById('returnStep2').classList.add('hidden');
      document.getElementById('returnStep3').classList.add('hidden');
      document.getElementById('returnError').classList.add('hidden');
  }
  </script>
  @endpush











<script>
// Debounce básico
let _returnSuggestTimer = null;

async function suggestReturnProducts() {
  const q = document.getElementById('returnProductSearch').value.trim();
  const box = document.getElementById('returnProductSuggestions');

  if (_returnSuggestTimer) clearTimeout(_returnSuggestTimer);

  _returnSuggestTimer = setTimeout(async () => {
    if (!q) { box.classList.add('hidden'); box.innerHTML=''; return; }

    try {
      const r = await fetch(`/sales-management/products/suggest?q=${encodeURIComponent(q)}`, {
        headers: { 'Accept':'application/json' }
      });
      const items = r.ok ? await r.json() : [];

      if (!items.length) {
        box.innerHTML = `<div class="px-3 py-2 text-sm text-gray-500">Sin resultados</div>`;
        box.classList.remove('hidden');
        return;
      }

      box.innerHTML = items.map(p => `
        <button class="w-full text-left px-3 py-2 hover:bg-blue-50 flex items-center gap-2"
                onclick='selectProductForReturn(${JSON.stringify(p)})'>
          <span class="font-medium">${p.name}</span>
          <span class="text-xs text-gray-500 ml-2">${p.barcode ?? ''}</span>
          <span class="text-xs text-gray-500 ml-auto">${p.category ?? ''}</span>
        </button>
      `).join('');

      box.classList.remove('hidden');
    } catch {
      box.innerHTML = '';
      box.classList.add('hidden');
    }
  }, 200);
}

function selectProductForReturn(p) {
  // Guarda selección "global" reutilizando tu variable ya existente
  selectedProductForReturn = p;

  // Rellena input con el nombre elegido
  const input = document.getElementById('returnProductSearch');
  input.value = p.name;

  // Oculta sugerencias
  const box = document.getElementById('returnProductSuggestions');
  box.innerHTML = '';
  box.classList.add('hidden');

  // Dispara la búsqueda en ventas del turno con el ID correcto
  searchProductInShiftById(p.id);
}

async function searchProductInShiftById(productId) {
  try {
    const response = await fetch('/sales-management/search-product-in-shift', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ product_id: productId })
    });

    const data = await response.json();

    if (!data.ok) {
      showReturnError(data.message + (data.detail ? '\n\n' + data.detail : ''));
      document.getElementById('returnStep2').classList.add('hidden');
      document.getElementById('returnStep3').classList.add('hidden');
      return;
    }

    document.getElementById('returnError').classList.add('hidden');
    
    // ============================================
    // ✅ YA NO FILTRAMOS - MOSTRAMOS TODAS
    // ============================================
    // Simplemente pasamos todas las ventas a la función de display
    displaySalesForReturn(data.sales);
    
  } catch (e) {
    console.error(e);
    showReturnError('Error al buscar ventas');
  }
}
</script>

















  {{-- MODALES DE VENTAS EN ESPERA Y DEVOLUCIONES --}}
  {{-- Modal: Confirmar poner en espera --}}
  <div id="holdSaleModal" 
       class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
          <h3 class="text-xl font-bold mb-4">⏸️ Poner Venta en Espera</h3>
          
          <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                  Nombre del cliente (opcional):
              </label>
              <input type="text" 
                     id="holdCustomerName" 
                     placeholder="Ej: Juan Pérez"
                     class="w-full rounded-md border-gray-300 shadow-sm">
          </div>
          
          <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                  Notas (opcional):
              </label>
              <textarea id="holdNotes" 
                        rows="2"
                        placeholder="Ej: Cliente volverá en 10 minutos"
                        class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
          </div>
          
          <div class="flex gap-2">
              <button onclick="confirmHoldSale()" 
                      class="flex-1 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg">
                  Guardar
              </button>
              <button onclick="closeHoldModal()" 
                      class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold rounded-lg">
                  Cancelar
              </button>
          </div>
      </div>
  </div>

  {{-- Modal: Ventas en espera --}}
  <div id="pendingSalesModal" 
       class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-lg bg-white">
          
          <div class="flex justify-between items-center pb-3 border-b">
              <h3 class="text-2xl font-bold text-gray-900">
                  📋 Ventas en Espera
              </h3>
              <button onclick="closePendingSalesModal()" 
                      class="text-gray-400 hover:text-gray-600 text-3xl font-bold">
                  &times;
              </button>
          </div>
          
          <div class="mt-4">
              <div id="pendingSalesList" class="space-y-3">
              </div>
              
              <div id="noPendingSales" class="hidden text-center py-8 text-gray-500">
                  <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                  </svg>
                  <p class="text-lg font-medium">No hay ventas en espera</p>
              </div>
          </div>
          
      </div>
  </div>

  {{-- Modal: Devolución --}}
  <div id="returnModal" 
       class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-lg bg-white">
          
          <div class="flex justify-between items-center pb-3 border-b">
              <h3 class="text-2xl font-bold text-gray-900">
                  🔄 Devolución de Producto
              </h3>
              <button onclick="closeReturnModal()" 
                      class="text-gray-400 hover:text-gray-600 text-3xl font-bold">
                  &times;
              </button>
          </div>
          
          <div class="mt-4">
              
              <div id="returnStep1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                      Buscar producto a devolver:
                  </label>
                  <div class="flex gap-2">
                     <input type="text"
       id="returnProductSearch"
       placeholder="Código de barras o nombre del producto"
       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
       oninput="suggestReturnProducts()"
       onkeypress="if(event.key==='Enter'){ 
         const box=document.getElementById('returnProductSuggestions');
         // Si hay sugerencias abiertas, ignora Enter para no cerrar
         if(box && !box.classList.contains('hidden')){ event.preventDefault(); return; }
         searchProductForReturn(); 
       }">

                             <div id="returnProductSuggestions" class="mt-2 border rounded-lg max-h-56 overflow-y-auto hidden bg-white shadow">
  <!-- Aquí se inyectan las sugerencias -->
</div>

                      <button onclick="searchProductForReturn()" 
                              class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                          🔍 Buscar
                      </button>
                  </div>
              </div>
              
              <div id="returnStep2" class="hidden mt-6">
                  <h4 class="text-lg font-semibold mb-3">Ventas de hoy con este producto:</h4>
                  <div id="returnSalesList" class="space-y-2 max-h-64 overflow-y-auto">
                  </div>
              </div>
              
              <div id="returnStep3" class="hidden mt-6">
                  <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-4">
                      <h4 class="text-lg font-semibold mb-2">Confirmar Devolución</h4>
                      <p class="text-sm text-gray-600" id="selectedSaleInfo"></p>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      
                      <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">
                              Cantidad a devolver:
                          </label>
                          <input type="number" 
                                 id="returnQty" 
                                 min="0.01" 
                                 step="0.01"
                                 class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                          <small class="text-gray-500">
                              Máximo: <span id="returnMaxQty" class="font-semibold">0</span>
                          </small>
                      </div>
                      
                      <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">
                              Razón de devolución:
                          </label>
                          <select id="returnReason" 
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                              <option value="">Selecciona...</option>
                              <option value="Producto defectuoso">Producto defectuoso</option>
                              <option value="Cliente cambió de opinión">Cliente cambió de opinión</option>
                              <option value="Error al cobrar">Error al cobrar</option>
                              <option value="Producto vencido">Producto vencido</option>
                              <option value="Otro">Otro</option>
                          </select>
                      </div>
                      
                  </div>
                  
                  <div class="mt-4 bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4">
                      <label class="block text-lg font-bold text-gray-900 mb-3">
                          ⚠️ ¿En qué estado está el producto?
                      </label>
                      
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                          <label class="flex items-center gap-3 p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-white transition"
                                 onclick="document.getElementById('conditionGood').checked = true; updateConditionWarning()">
                              <input type="radio" 
                                     id="conditionGood"
                                     name="productCondition" 
                                     value="good" 
                                     checked
                                     class="w-5 h-5">
                              <div>
                                  <div class="text-green-700 font-semibold text-lg">
                                      ✅ BUEN ESTADO
                                  </div>
                                  <small class="text-gray-600">
                                      Se puede volver a vender
                                  </small>
                              </div>
                          </label>
                          
                          <label class="flex items-center gap-3 p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-white transition"
                                 onclick="document.getElementById('conditionDamaged').checked = true; updateConditionWarning()">
                              <input type="radio" 
                                     id="conditionDamaged"
                                     name="productCondition" 
                                     value="damaged"
                                     class="w-5 h-5">
                              <div>
                                  <div class="text-red-700 font-semibold text-lg">
                                      ❌ MAL ESTADO
                                  </div>
                                  <small class="text-gray-600">
                                      No se puede vender (MERMA)
                                  </small>
                              </div>
                          </label>
                      </div>
                      
                      <div id="conditionWarning" class="hidden mt-3 p-3 bg-red-100 border border-red-400 rounded text-red-800">
                          <strong>⚠️ IMPORTANTE:</strong> Este producto NO regresará al inventario. 
                          Se registrará como PÉRDIDA/MERMA.
                      </div>
                  </div>
                  
                  <div class="flex gap-3 mt-6">
                      <button onclick="processReturn()" 
                              class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-lg">
                          💰 CONFIRMAR DEVOLUCIÓN
                      </button>
                      <button onclick="cancelReturn()" 
                              class="px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg">
                          ❌ Cancelar
                      </button>
                  </div>
              </div>
              
              <div id="returnError" class="hidden mt-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                  <div class="flex">
                      <div class="flex-shrink-0">
                          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                          </svg>
                      </div>
                      <div class="ml-3">
                          <p id="returnErrorMessage" class="text-sm font-medium"></p>
                      </div>
                  </div>
              </div>
              
          </div>
      </div>
  </div>

</x-app-layout>