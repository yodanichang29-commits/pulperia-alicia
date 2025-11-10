<?php

use Illuminate\Support\Facades\Route;

// ===== Controladores =====
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\Caja\ClientPaymentController;
use App\Http\Controllers\Reportes\ReporteVentasController;
use App\Http\Controllers\Reportes\ReporteCxCController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryMovementsController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaleManagementController;

/*
|--------------------------------------------------------------------------
| HOME / DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => auth()->check() ? redirect()->route('caja') : redirect()->route('login'));
Route::get('/dashboard', fn () => redirect()->route('caja'))
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| CAJA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/caja', [CajaController::class, 'index'])->name('caja');
    Route::post('/caja/cobrar', [CajaController::class, 'charge'])->name('caja.charge');

    Route::get('/caja/barcode/{code}', [CajaController::class, 'barcode'])
        ->where('code', '[A-Za-z0-9\-\_\.]+')
        ->name('caja.barcode');

    // Clientes (CxC)
    Route::get('/caja/clientes', [CajaController::class, 'clients'])->name('caja.clients');
    Route::post('/caja/clientes', [CajaController::class, 'storeClient'])->name('caja.clients.store');

    // Abonos de clientes
    Route::post('/caja/clientes/{client}/abono', [ClientPaymentController::class, 'store'])
        ->name('caja.clients.pay');

    // Turnos
    Route::get('/caja/shift/current', [ShiftController::class, 'current'])->name('caja.shift.current');
    Route::post('/caja/shift/open', [ShiftController::class, 'open'])->name('caja.shift.open');
    Route::get('/caja/shift/summary/{id?}', [ShiftController::class, 'summary'])->name('caja.shift.summary');
    Route::post('/caja/shift/close', [ShiftController::class, 'close'])->name('caja.shift.close');
});

// Anular venta (desde Caja)
Route::middleware('auth')
    ->post('/caja/ventas/{sale}/anular', [SaleController::class, 'void'])
    ->name('caja.sales.void');

/*
|--------------------------------------------------------------------------
| PERFIL (Jetstream / Breeze)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| REPORTES: CxC
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/reportes/cxc',            [ReporteCxCController::class, 'index'])->name('reportes.cxc');
    Route::get('/reportes/cxc/export',     [ReporteCxCController::class, 'exportCsv'])->name('reportes.cxc.export');
    Route::get('/reportes/cxc/{client}',   [ReporteCxCController::class, 'show'])->name('reportes.cxc.show');
});

/*
|--------------------------------------------------------------------------
| REPORTES: Ventas (UN SOLO BLOQUE, nombres consistentes)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('reportes/ventas')
    ->name('reportes.ventas.')
    ->group(function () {
        Route::get('/',               [ReporteVentasController::class, 'index'])->name('index');
        Route::get('/export',         [ReporteVentasController::class, 'exportCsv'])->name('export');
        Route::get('/export-excel',   [ReporteVentasController::class, 'exportExcel'])->name('excel');
        Route::get('/proveedores',    [ReporteVentasController::class, 'porProveedor'])->name('proveedores');

        // NUEVO: ventas por producto (vista + filtro)
        Route::get('/por-producto',   [ReporteVentasController::class, 'porProducto'])->name('producto');


        Route::get('/turnos',         [ReporteVentasController::class, 'turnos'])->name('turnos');
        Route::get('/turnos/{id}',    [ReporteVentasController::class, 'turnoDetalle'])->name('turnos.detalle');

        // NUEVO: autocomplete productos (JSON)
        Route::get('/buscar-productos', [ReporteVentasController::class, 'buscarProductos'])->name('buscar_productos');
    });

/*
|--------------------------------------------------------------------------
| INVENTARIO (vista y ajustes rápidos)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/inventario',                        [InventarioController::class, 'index'])->name('inventario.index');
    Route::post('/inventario/{product}/ajustar',     [InventarioController::class, 'adjust'])->name('inventario.adjust');
    Route::patch('/inventario/{product}/min',        [InventarioController::class, 'updateMin'])->name('inventario.min');
});

/*
|--------------------------------------------------------------------------
| PRODUCTOS (CRUD + búsqueda para autocompletar)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/productos/create',            [ProductController::class, 'create'])->name('productos.create');
    Route::get('/productos/{product}/edit',    [ProductController::class, 'edit'])->name('productos.edit');
    Route::post('/productos',                  [ProductController::class, 'store'])->name('productos.store');
    Route::patch('/productos/{product}',       [ProductController::class, 'update'])->name('productos.update');

    // Autocompletar productos (si lo usas en otras vistas)
    Route::get('/productos/buscar',            [ProductController::class, 'buscar'])->name('productos.buscar');
});

/*
|--------------------------------------------------------------------------
| MOVIMIENTOS DE INVENTARIO (legacy)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/movimientos/nuevo', [InventoryMovementsController::class, 'create'])->name('movimientos.create');
    Route::post('/movimientos',      [InventoryMovementsController::class, 'store'])->name('movimientos.store');
});

/*
|--------------------------------------------------------------------------
| PROVEEDORES (CRUD + autocompletar)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::resource('proveedores', ProviderController::class)->except(['show']);
    Route::get('/proveedores/buscar', [ProviderController::class, 'buscar'])->name('proveedores.buscar');
});

/*
|--------------------------------------------------------------------------
| API DE VENTAS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->post('/sales', [SaleController::class, 'store'])->name('sales.store');

/*
|--------------------------------------------------------------------------
| INGRESOS Y EGRESOS (Módulo principal)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('ingresos-egresos')
    ->name('ingresos.')
    ->group(function () {
        Route::get('/',                   [InventoryTransactionController::class, 'index'])->name('index');
        Route::get('/nuevo',              [InventoryTransactionController::class, 'create'])->name('create');
        Route::post('/',                  [InventoryTransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}',      [InventoryTransactionController::class, 'show'])->name('show');
        Route::post('/{transaction}/void',[InventoryTransactionController::class, 'void'])->name('void');
    });

/*
|--------------------------------------------------------------------------
| FINANZAS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/finanzas', [FinanceController::class, 'index'])->name('finanzas.index');



Route::get('/reportes/ventas/detalle', [\App\Http\Controllers\Reportes\ReporteVentasController::class, 'detalle'])
    ->name('reportes.ventas.detalle');



Route::get('/reportes/ventas/{id}', [\App\Http\Controllers\Reportes\ReporteVentasController::class, 'show'])
    ->name('reportes.ventas.show');




// Dashboard analítico
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard'); 







/*
|--------------------------------------------------------------------------
| GESTIÓN DE VENTAS (Devoluciones y Ventas en Espera)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| GESTIÓN DE VENTAS (Devoluciones y Ventas en Espera)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('sales-management')
    ->name('sales.')
    ->group(function () {
        // Ventas en espera
        Route::post('/hold', [SaleManagementController::class, 'holdSale'])
            ->name('hold');
        
        Route::get('/pending', [SaleManagementController::class, 'listPendingSales'])
            ->name('pending.list');
        
        Route::get('/pending/{id}', [SaleManagementController::class, 'retrievePendingSale'])
            ->name('pending.retrieve');
        
        Route::delete('/pending/{id}', [SaleManagementController::class, 'deletePendingSale'])
            ->name('pending.delete');
        
        Route::post('/pending/{id}/complete', [SaleManagementController::class, 'completePendingSale'])
            ->name('pending.complete');
        
        // Devoluciones - CON VALIDACIÓN Y MERMA
        Route::post('/search-product-in-shift', [SaleManagementController::class, 'searchProductInCurrentShift'])
            ->name('search.product');
        
        Route::post('/return-item', [SaleManagementController::class, 'returnSaleItem'])
            ->name('return.item');
    });





    Route::get('/sales-management/products/suggest', [SaleManagementController::class, 'suggestProducts'])
    ->name('sales.products.suggest')
    ->middleware('auth');





// ✅ Ruta para validar si un producto está vencido
Route::get('/api/products/{product}/check-expiry', function (\App\Models\Product $product) {
    $today = now()->startOfDay();
    $expired = false;
    
    if ($product->expires_at) {
        $expiresAt = \Carbon\Carbon::parse($product->expires_at)->startOfDay();
        $expired = $expiresAt->lessThan($today);
    }
    
    return response()->json([
        'id' => $product->id,
        'name' => $product->name,
        'expires_at' => $product->expires_at ? \Carbon\Carbon::parse($product->expires_at)->format('d/m/Y') : null,
        'expired' => $expired
    ]);
});






/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
