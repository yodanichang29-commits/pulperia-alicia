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

    @if(!empty($product->image_path))
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




  <div>
    <label class="block text-sm font-medium text-gray-700">Precio venta</label>
    <input name="price" type="number" step="0.01" min="0" required
           value="{{ old('price', $product->price) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    @error('price')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Costo compra</label>
    <input name="purchase_price" type="number" step="0.01" min="0"
           value="{{ old('purchase_price', $product->purchase_price) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
    @error('purchase_price')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Unidad</label>
    <input name="unit" type="text" placeholder="unidad, litro, kg, paquete…"
           value="{{ old('unit', $product->unit) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Proveedor</label>
    <select name="provider_id"
            class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      <option value="">— Sin proveedor —</option>
      @foreach($providers as $prov)
        <option value="{{ $prov->id }}" {{ old('provider_id', $product->provider_id) == $prov->id ? 'selected' : '' }}>
          {{ $prov->name }}
        </option>
      @endforeach
    </select>
    @error('provider_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Stock</label>
    <input name="stock" type="number" min="0" required
           value="{{ old('stock', $product->stock) }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
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

  <div class="flex items-center gap-2">
    <input id="active" name="active" type="checkbox" value="1"
           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
           {{ old('active', $product->active) ? 'checked' : '' }}>
    <label for="active" class="text-sm text-gray-700">Activo</label>
  </div>
</div>

<div class="mt-6 flex items-center gap-3">
  <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
    {{ $btn ?? 'Guardar' }}
  </button>
  <a href="{{ route('inventario.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
