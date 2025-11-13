{{-- Tabla: resumen por categoría --}}
@include('reportes.ventas.partials.categorias_tabla', ['rows'=>$rows,'total_general'=>$total_general])

{{-- Tabla: detalle por producto (según filtros / búsqueda) --}}
<div class="mt-5">
  <h3 class="text-gray-800 font-semibold mb-2">Detalle de productos</h3>
  @include('reportes.ventas.partials.categorias_detalle_productos', ['detalle'=>$detalle])
</div>
