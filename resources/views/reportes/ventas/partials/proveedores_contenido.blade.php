{{-- Tabla: resumen por proveedor --}}
@include('reportes.ventas.partials.proveedores_tabla', ['rows'=>$rows,'total_general'=>$total_general])

{{-- Tabla: detalle por producto (según filtros / búsqueda) --}}
<div class="mt-5">
  <h3 class="text-gray-800 font-semibold mb-2">Detalle de productos</h3>
  @include('reportes.ventas.partials.proveedores_detalle_productos', ['detalle'=>$detalle])
</div>
