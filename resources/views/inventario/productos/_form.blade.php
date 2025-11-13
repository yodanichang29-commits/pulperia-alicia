@csrf
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

{{-- Foto del producto --}}
<div class="sm:col-span-2">
  <label class="block text-sm font-medium text-gray-700">Foto</label>

  <div class="mt-1 flex items-center gap-4">
    <img id="preview-img"
         src="{{ $product->image_url ?? '' }}"
         alt="Previsualización"
         class="h-20 w-20 object-cover rounded border {{ ($product->image_url ?? false) ? '' : 'hidden' }}">

    <input type="file" name="image" id="image" accept="image/*"
           class="block w-full text-sm text-gray-700
                  file:mr-4 file:py-2 file:px-3
                  file:rounded-lg file:border-0
                  file:text-sm file:font-semibold
                  file:bg-indigo-50 file:text-indigo-700
                  hover:file:bg-indigo-100">

    @if(!empty($product->photo))
      <label class="inline-flex items-center gap-2 ms-2">
        <input type="checkbox" name="remove_image" value="1"
               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <span class="text-sm text-gray-700">Quitar foto actual</span>
      </label>
    @endif
  </div>

  @error('image')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('image');
  const img   = document.getElementById('preview-img');
  if(!input || !img) return;

  input.addEventListener('change', () => {
    const [file] = input.files || [];
    if (!file) return;
    img.src = URL.createObjectURL(file);
    img.classList.remove('hidden');
  });
});
</script>
@endpush




  <div>
    <label class="block text-sm font-medium text-gray-700">Nombre</label>
    <input name="name" type="text" required value="{{ old('name', $product->name) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Código de barras</label>
    <input name="barcode" type="text" value="{{ old('barcode', $product->barcode) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    @error('barcode')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  {{-- 👈 MODIFICADO: Agregamos ID para controlarlo con JavaScript --}}
  <div>
    <label class="block text-sm font-medium text-gray-700">Precio venta</label>
    <input id="precio-venta" name="price" type="number" step="0.01" min="0" required
           value="{{ old('price', $product->price) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    @error('price')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  {{-- 👈 MODIFICADO: Agregamos ID para controlarlo con JavaScript --}}
  <div>
    <label class="block text-sm font-medium text-gray-700">Costo compra</label>
    <input id="costo-compra" name="cost" type="number" step="0.01" min="0"
           value="{{ old('cost', $product->cost) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
    @error('cost')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  {{-- 👈 NUEVO: Campo de Margen Comercial % --}}
  <div>
    <label class="block text-sm font-medium text-gray-700">Margen Comercial %</label>
    <input id="margen-comercial" type="number" step="0.01" min="0"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
           placeholder="Ej: 20 (significa 20% sobre el costo)">
    <p class="text-xs text-gray-500 mt-1">Cuánto % le subes al costo de compra</p>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Stock</label>
    <input name="stock" type="number" min="0" required
           value="{{ old('stock', $product->stock) }}"
           {{ $product->exists ? 'readonly' : '' }}
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ $product->exists ? 'bg-gray-100 cursor-not-allowed' : '' }}">
    @if($product->exists)
      <p class="text-xs text-gray-500 mt-1">El stock solo se modifica con movimientos de inventario o ventas</p>
    @endif
    @error('stock')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Mínimo</label>
    <input name="min_stock" type="number" min="0" required
           value="{{ old('min_stock', $product->min_stock) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    @error('min_stock')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  <div class="flex items-center gap-2">
    <input id="active" name="active" type="checkbox" value="1"
           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
           {{ old('active', $product->active) ? 'checked' : '' }}>
    <label for="active" class="text-sm text-gray-700">Activo</label>
  </div>
</div>

<div class="flex gap-3 mt-6">
  <button type="submit"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    {{ $btn ?? 'Guardar' }}
  </button>
  <a href="{{ route('inventario.index') }}"
     class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
    Cancelar
  </a>
</div>

{{-- 👈 NUEVO: JavaScript para calcular automáticamente --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtenemos los 3 campos importantes
    const precioVenta = document.getElementById('precio-venta');
    const costoCompra = document.getElementById('costo-compra');
    const margenComercial = document.getElementById('margen-comercial');

    // Variable para evitar bucles infinitos
    let calculando = false;

    // Función para calcular el margen % cuando cambia el precio
    function calcularMargenPorcentaje() {
        if (calculando) return; // Si ya estamos calculando, no hacer nada
        
        const costo = parseFloat(costoCompra.value) || 0;
        const precio = parseFloat(precioVenta.value) || 0;

        // Si el costo es 0, no podemos calcular el margen
        if (costo === 0) {
            margenComercial.value = '';
            return;
        }

        // Fórmula: Margen% = ((Precio - Costo) / Costo) × 100
        const margen = ((precio - costo) / costo) * 100;
        
        calculando = true;
        margenComercial.value = margen.toFixed(2); // Redondeamos a 2 decimales
        calculando = false;
    }

    // Función para calcular el precio cuando cambia el margen %
    function calcularPrecioVenta() {
        if (calculando) return; // Si ya estamos calculando, no hacer nada
        
        const costo = parseFloat(costoCompra.value) || 0;
        const margen = parseFloat(margenComercial.value) || 0;

        // Fórmula: Precio = Costo × (1 + Margen%/100)
        const precio = costo * (1 + margen / 100);
        
        calculando = true;
        precioVenta.value = precio.toFixed(2); // Redondeamos a 2 decimales
        calculando = false;
    }

    // Cuando carga la página, calculamos el margen inicial
    if (precioVenta.value && costoCompra.value) {
        calcularMargenPorcentaje();
    }

    // Escuchamos cambios en el PRECIO DE VENTA
    precioVenta.addEventListener('input', calcularMargenPorcentaje);

    // Escuchamos cambios en el COSTO DE COMPRA
    // (puede afectar tanto el margen como el precio)
    costoCompra.addEventListener('input', function() {
        // Si ya hay un margen definido, recalculamos el precio
        if (margenComercial.value) {
            calcularPrecioVenta();
        } else {
            // Si no hay margen, recalculamos el margen basado en el precio actual
            calcularMargenPorcentaje();
        }
    });

    // Escuchamos cambios en el MARGEN COMERCIAL %
    margenComercial.addEventListener('input', calcularPrecioVenta);
});
</script>
@endpush