<?php
/**
 * Script de diagnóstico para verificar el sistema de pagos
 *
 * Ejecutar desde la raíz del proyecto:
 * php diagnostico.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNÓSTICO DEL SISTEMA DE PAGOS ===\n\n";

// 1. Verificar conexión a base de datos
echo "1. CONEXIÓN A BASE DE DATOS\n";
try {
    $connection = DB::connection()->getDatabaseName();
    $driver = DB::connection()->getDriverName();
    echo "   ✓ Conectado a: {$connection}\n";
    echo "   ✓ Driver: {$driver}\n";
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
    echo "\n   SOLUCIÓN: Verifica tu archivo .env y asegúrate de que la base de datos esté corriendo.\n";
    exit(1);
}

// 2. Verificar si existen las tablas
echo "\n2. TABLAS NECESARIAS\n";
$tables = ['purchase_payments', 'cash_movements', 'inventory_transactions'];
foreach ($tables as $table) {
    $exists = Schema::hasTable($table);
    if ($exists) {
        $count = DB::table($table)->count();
        echo "   ✓ Tabla '{$table}' existe ({$count} registros)\n";

        // Verificar columnas específicas
        if ($table === 'purchase_payments') {
            $hasColumn = Schema::hasColumn($table, 'payment_method');
            echo "     " . ($hasColumn ? "✓" : "✗") . " Columna 'payment_method' " . ($hasColumn ? "existe" : "NO existe") . "\n";
        }
        if ($table === 'cash_movements') {
            $hasColumn = Schema::hasColumn($table, 'cash_shift_id');
            echo "     " . ($hasColumn ? "✓" : "✗") . " Columna 'cash_shift_id' " . ($hasColumn ? "existe" : "NO existe") . "\n";
        }
    } else {
        echo "   ✗ Tabla '{$table}' NO existe\n";
    }
}

// 3. Verificar migraciones pendientes
echo "\n3. MIGRACIONES PENDIENTES\n";
try {
    $migrator = app('migrator');
    $ran = $migrator->getRepository()->getRan();
    $migrations = $migrator->getMigrationFiles($migrator->paths());

    $pending = [];
    foreach ($migrations as $migration) {
        if (!in_array($migration, $ran)) {
            $pending[] = $migration;
        }
    }

    if (empty($pending)) {
        echo "   ✓ No hay migraciones pendientes\n";
    } else {
        echo "   ✗ HAY " . count($pending) . " MIGRACIONES PENDIENTES:\n";
        foreach ($pending as $p) {
            echo "     - {$p}\n";
        }
        echo "\n   SOLUCIÓN: Ejecuta 'php artisan migrate' para aplicar las migraciones.\n";
    }
} catch (\Exception $e) {
    echo "   ✗ ERROR al verificar migraciones: " . $e->getMessage() . "\n";
}

// 4. Verificar últimas compras
echo "\n4. ÚLTIMAS COMPRAS (INVENTORY_TRANSACTIONS)\n";
try {
    $purchases = DB::table('inventory_transactions')
        ->where('type', 'in')
        ->where('reason', 'purchase')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get(['id', 'total_cost', 'created_at']);

    if ($purchases->isEmpty()) {
        echo "   - No hay compras registradas aún\n";
    } else {
        foreach ($purchases as $p) {
            $paymentsCount = DB::table('purchase_payments')->where('purchase_id', $p->id)->count();
            echo "   - Compra #{$p->id}: L{$p->total_cost} ({$p->created_at}) - {$paymentsCount} pagos\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 5. Probar creación de pago (simulación)
echo "\n5. TEST DE VALIDACIÓN\n";
try {
    $validator = Validator::make([
        'payments' => [
            ['method' => 'caja', 'amount' => 100, 'affects_cash' => '1', 'notes' => 'test']
        ]
    ], [
        'payments' => 'nullable|array|min:1',
        'payments.*.method' => 'nullable|in:caja,efectivo_personal,credito,transferencia,tarjeta',
        'payments.*.amount' => 'nullable|numeric|min:0',
        'payments.*.affects_cash' => 'nullable',
        'payments.*.notes' => 'nullable|string|max:500',
    ]);

    if ($validator->passes()) {
        echo "   ✓ Validación de pagos funciona correctamente\n";
    } else {
        echo "   ✗ Validación de pagos falló:\n";
        foreach ($validator->errors()->all() as $error) {
            echo "     - {$error}\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n\n";

// Resumen
$allOk = Schema::hasTable('purchase_payments') &&
         Schema::hasTable('cash_movements') &&
         Schema::hasColumn('cash_movements', 'cash_shift_id');

if ($allOk) {
    echo "✓ El sistema está configurado correctamente.\n";
    echo "  Si las compras no se guardan, revisa los logs en storage/logs/laravel.log\n";
} else {
    echo "✗ HAY PROBLEMAS DE CONFIGURACIÓN.\n";
    echo "  SOLUCIÓN: Ejecuta 'php artisan migrate' para crear las tablas necesarias.\n";
}

echo "\n";
