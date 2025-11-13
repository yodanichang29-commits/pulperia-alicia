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
        $this->info('🔧 Arreglando tabla purchase_payments...');
        $this->newLine();

        try {
            DB::statement('PRAGMA foreign_keys = OFF');

            // 1. Respaldar datos existentes
            $this->info('📦 Respaldando datos existentes...');
            $existingPayments = DB::table('purchase_payments')->get();
            $this->info("   Encontrados {$existingPayments->count()} registros");
            $this->newLine();

            // 2. Eliminar tabla completamente
            $this->info('🗑️  Eliminando tabla antigua...');
            DB::statement('DROP TABLE IF EXISTS purchase_payments');
            $this->info('   ✓ Tabla eliminada');
            $this->newLine();

            // 3. Recrear tabla con estructura correcta (sin ENUM)
            $this->info('🔨 Recreando tabla con estructura correcta...');
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
            $this->info('   ✓ Tabla recreada');
            $this->newLine();

            // 4. Restaurar datos, mapeando 'externo' → 'efectivo_personal'
            if ($existingPayments->count() > 0) {
                $this->info('📥 Restaurando datos...');
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

            DB::statement('PRAGMA foreign_keys = ON');

            $this->newLine();
            $this->info('✅ ¡Tabla purchase_payments arreglada exitosamente!');
            $this->newLine();
            $this->info('Ahora puedes:');
            $this->info('1. Crear compras con método de pago "efectivo_personal"');
            $this->info('2. Usar cualquiera de los 5 métodos de pago sin errores');
            $this->newLine();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Error: ' . $e->getMessage());
            $this->newLine();

            return Command::FAILURE;
        }
    }
}
