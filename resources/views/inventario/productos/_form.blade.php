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
    <input id="costo-compra" name="purchase_price" type="number" step="0.01" min="0"
           value="{{ old('purchase_price', $product->purchase_price) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
    @error('purchase_price')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
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

  <div>
    <label class="block text-sm font-medium text-gray-700">Caducidad (opcional)</label>
    <input name="expires_at" type="date"
           value="{{ old('expires_at', optional($product->expires_at)->format('Y-m-d')) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
  </div>

  {{-- Selector de Categoría --}}
<div>
  <label class="block text-sm font-medium text-gray-700">Categoría</label>
  <select name="category_id" 
          id="category_id"
          class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror">
    <option value="">Sin categoría</option>
    @foreach($categories as $cat)
      <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
        {{ $cat->name }}
      </option>
    @endforeach
  </select>
  @error('category_id')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
  @enderror
  <p class="text-xs text-gray-500 mt-1">Selecciona la categoría para agrupar en reportes</p>
</div>



{{-- 👈 NUEVO: Checkbox para marcar como paquete --}}
<div class="sm:col-span-2 border-t pt-4 mt-4">
  <div class="flex items-center gap-2 mb-4">
    <input id="is_package" 
           name="is_package" 
           type="checkbox" 
           value="1"
           {{ old('is_package', $product->is_package) ? 'checked' : '' }}
           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
    <label for="is_package" class="text-sm font-semibold text-gray-700">
      📦 Este producto es un PAQUETE (contiene varias unidades de otro producto)
    </label>
  </div>
  @error('is_package')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
</div>

{{-- 👈 NUEVO: Campos que solo aparecen si es paquete --}}
<div id="package-fields" class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-indigo-50 p-4 rounded-lg {{ old('is_package', $product->is_package) ? '' : 'hidden' }}">
  
 <div>
  <label class="block text-sm font-medium text-gray-700">Producto Individual que Contiene</label>
  
  {{-- Campo de búsqueda visible --}}
  <div class="relative">
    <input type="text" 
           id="product-search" 
           placeholder="Escribe para buscar el producto..."
           value="{{ old('parent_product_name', optional($product->parentProduct)->name) }}"
           autocomplete="off"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    
    {{-- Icono de búsqueda --}}
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
    </div>
    
    {{-- Resultados de búsqueda --}}
    <div id="search-results" 
         class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto hidden">
      {{-- Los resultados aparecerán aquí --}}
    </div>
  </div>
  
  {{-- Campo oculto que guarda el ID real --}}
  <input type="hidden" 
         name="parent_product_id" 
         id="parent_product_id" 
         value="{{ old('parent_product_id', $product->parent_product_id) }}">
  
  <p class="text-xs text-gray-600 mt-1">¿Qué producto individual contiene este paquete?</p>
  @error('parent_product_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Cantidad de Unidades en el Paquete</label>
    <input name="units_per_package" 
           type="number" 
           min="1" 
           value="{{ old('units_per_package', $product->units_per_package) }}"
           placeholder="Ej: 20"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    <p class="text-xs text-gray-600 mt-1">¿Cuántas unidades individuales tiene el paquete?</p>
    @error('units_per_package')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="sm:col-span-2 bg-blue-50 border border-blue-200 rounded p-3">
    <p class="text-sm text-blue-800">
      <strong>ℹ️ Nota importante:</strong> Los paquetes NO tienen stock propio. 
      El stock se maneja en el producto individual. Al vender un paquete, 
      se restará automáticamente la cantidad correspondiente del producto individual.
    </p>
  </div>
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




    // 👈 NUEVO: JavaScript para mostrar/ocultar campos de paquete
    const checkboxPaquete = document.getElementById('is_package');
    const camposPaquete = document.getElementById('package-fields');
    const campoStock = document.querySelector('input[name="stock"]');

    if (checkboxPaquete && camposPaquete) {
        checkboxPaquete.addEventListener('change', function() {
            if (this.checked) {
                camposPaquete.classList.remove('hidden');
                // Si es paquete, el stock debe ser 0 y readonly
                if (campoStock) {
                    campoStock.value = 0;
                    campoStock.readOnly = true;
                    campoStock.classList.add('bg-gray-100', 'cursor-not-allowed');
                }
            } else {
                camposPaquete.classList.add('hidden');
                // Si no es paquete, permitir editar stock (solo en create)
                if (campoStock && !campoStock.hasAttribute('readonly')) {
                    campoStock.readOnly = false;
                    campoStock.classList.remove('bg-gray-100', 'cursor-not-allowed');
                }
            }
        });
    }






// ==========================================
    // BUSCADOR DE PRODUCTOS EN TIEMPO REAL
    // ==========================================
    const searchInput = document.getElementById('product-search');
    const searchResults = document.getElementById('search-results');
    const hiddenInput = document.getElementById('parent_product_id');
    let searchTimeout;

    if (searchInput && searchResults && hiddenInput) {
        // Cuando el usuario escribe en el campo
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Si no hay texto, ocultar resultados
            if (query.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }
            
            // Esperar 300ms antes de buscar (evitar búsquedas excesivas)
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                buscarProductos(query);
            }, 300);
        });
        
        // Función para buscar productos
        function buscarProductos(query) {
            // Hacer petición AJAX al servidor
            fetch(`/productos/buscar?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    mostrarResultados(data);
                })
                .catch(error => {
                    console.error('Error al buscar productos:', error);
                });
        }
        
        // Función para mostrar resultados
        function mostrarResultados(productos) {
            // Filtrar solo productos que NO son paquetes
            const productosIndividuales = productos.filter(p => !p.is_package);
            
            if (productosIndividuales.length === 0) {
                searchResults.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">No se encontraron productos</div>';
                searchResults.classList.remove('hidden');
                return;
            }
            
            // Crear HTML de resultados
            let html = '';
            productosIndividuales.forEach(producto => {
                html += `
                    <div class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-100 product-result" 
                         data-id="${producto.id}" 
                         data-name="${producto.name}">
                        <div class="font-medium text-gray-900">${producto.name}</div>
                        ${producto.barcode ? `<div class="text-xs text-gray-500">Código: ${producto.barcode}</div>` : ''}
                    </div>
                `;
            });
            
            searchResults.innerHTML = html;
            searchResults.classList.remove('hidden');
            
            // Agregar eventos de click a cada resultado
            document.querySelectorAll('.product-result').forEach(item => {
                item.addEventListener('click', function() {
                    seleccionarProducto(this.dataset.id, this.dataset.name);
                });
            });
        }
        
        // Función para seleccionar un producto
        function seleccionarProducto(id, name) {
            searchInput.value = name;
            hiddenInput.value = id;
            searchResults.classList.add('hidden');
        }
        
        // Cerrar resultados al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    }





});





</script>
@endpush