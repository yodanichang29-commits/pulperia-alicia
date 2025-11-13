<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPurchasePaymentsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:purchase-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Arregla la tabla purchase_payments eliminando el CHECK constraint del ENUM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Arreglando tablas purchase_payments y cash_movements...');
        $this->newLine();

        try {
            DB::statement('PRAGMA foreign_keys = OFF');

            // 1. Respaldar datos existentes de ambas tablas
            $this->info('📦 Respaldando datos existentes...');

            $existingPayments = [];
            try {
                $existingPayments = DB::table('purchase_payments')->get()->toArray();
                $this->info("   Encontrados {count($existingPayments)} pagos de compras");
            } catch (\Exception $e) {
                $this->warn("   No se pudieron leer pagos existentes (la tabla podría no existir)");
            }

            $existingCashMovements = DB::table('cash_movements')->get()->toArray();
            $this->info("   Encontrados " . count($existingCashMovements) . " movimientos de caja");
            $this->newLine();

            // 2. Eliminar tabla purchase_payments_old si existe
            $this->info('🗑️  Limpiando tablas temporales...');
            DB::statement('DROP TABLE IF EXISTS purchase_payments_old');
            $this->info('   ✓ Limpieza completada');
            $this->newLine();

            // 3. Eliminar y recrear purchase_payments
            $this->info('🗑️  Eliminando tabla purchase_payments antigua...');
            DB::statement('DROP TABLE IF EXISTS purchase_payments');
            $this->info('   ✓ Tabla eliminada');
            $this->newLine();

            $this->info('🔨 Recreando tabla purchase_payments con estructura correcta...');
            DB::statement("
                CREATE TABLE purchase_payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    purchase_id INTEGER NOT NULL,
                    amount NUMERIC(12, 2) NOT NULL,
                    payment_method VARCHAR NOT NULL,
                    affects_cash INTEGER NOT NULL DEFAULT 0,
                    notes TEXT,
                    user_id INTEGER NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY(purchase_id) REFERENCES inventory_transactions(id) ON DELETE CASCADE,
                    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            $this->info('   ✓ Tabla purchase_payments recreada');
            $this->newLine();

            // 4. Restaurar datos de purchase_payments
            if (count($existingPayments) > 0) {
                $this->info('📥 Restaurando pagos de compras...');
                foreach ($existingPayments as $payment) {
                    $paymentMethod = $payment->payment_method === 'externo' ? 'efectivo_personal' : $payment->payment_method;

                    DB::table('purchase_payments')->insert([
                        'id' => $payment->id,
                        'purchase_id' => $payment->purchase_id,
                        'amount' => $payment->amount,
                        'payment_method' => $paymentMethod,
                        'affects_cash' => $payment->affects_cash,
                        'notes' => $payment->notes,
                        'user_id' => $payment->user_id,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ]);

                    $this->info("   ✓ Restaurado pago #{$payment->id}");
                }
                $this->newLine();
            }

            // 5. Recrear tabla cash_movements para arreglar foreign key corrupta
            $this->info('🔨 Recreando tabla cash_movements para arreglar foreign keys...');

            DB::statement('DROP TABLE IF EXISTS cash_movements');

            // Obtener el schema de la migración original
            $this->info('   Leyendo estructura de cash_movements desde migraciones...');

            // Recrear la tabla completa (basado en la migración 2024_11_09_170036_create_cash_movements_table.php)
            DB::statement("
                CREATE TABLE cash_movements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    cash_shift_id INTEGER NOT NULL,
                    purchase_payment_id INTEGER,
                    date DATETIME NOT NULL,
                    type VARCHAR NOT NULL CHECK (type IN ('ingreso', 'egreso')),
                    category VARCHAR,
                    description TEXT NOT NULL,
                    amount NUMERIC(12, 2) NOT NULL,
                    payment_method VARCHAR NOT NULL,
                    notes TEXT,
                    created_by INTEGER NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY(cash_shift_id) REFERENCES cash_shifts(id) ON DELETE CASCADE,
                    FOREIGN KEY(purchase_payment_id) REFERENCES purchase_payments(id) ON DELETE SET NULL,
                    FOREIGN KEY(created_by) REFERENCES users(id)
                )
            ");
            $this->info('   ✓ Tabla cash_movements recreada');
            $this->newLine();

            // 6. Restaurar datos de cash_movements
            if (count($existingCashMovements) > 0) {
                $this->info('📥 Restaurando movimientos de caja...');
                foreach ($existingCashMovements as $movement) {
                    DB::table('cash_movements')->insert([
                        'id' => $movement->id,
                        'cash_shift_id' => $movement->cash_shift_id,
                        'purchase_payment_id' => $movement->purchase_payment_id ?? null,
                        'date' => $movement->date,
                        'type' => $movement->type,
                        'category' => $movement->category ?? null,
                        'description' => $movement->description,
                        'amount' => $movement->amount,
                        'payment_method' => $movement->payment_method,
                        'notes' => $movement->notes ?? null,
                        'created_by' => $movement->created_by,
                        'created_at' => $movement->created_at,
                        'updated_at' => $movement->updated_at,
                    ]);
                }
                $this->info("   ✓ Restaurados " . count($existingCashMovements) . " movimientos");
                $this->newLine();
            }

            DB::statement('PRAGMA foreign_keys = ON');

            $this->newLine();
            $this->info('✅ ¡Tablas arregladas exitosamente!');
            $this->newLine();
            $this->info('Ahora puedes:');
            $this->info('1. Crear compras con método de pago "efectivo_personal"');
            $this->info('2. Usar cualquiera de los 5 métodos de pago sin errores');
            $this->info('3. Los movimientos de caja están correctamente vinculados');
            $this->newLine();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            $this->newLine();

            return Command::FAILURE;
        }
    }
}
