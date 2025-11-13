<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para SQLite necesitamos recrear la tabla porque no soporta ALTER COLUMN
        // cuando hay CHECK constraints (que es como SQLite implementa los ENUMs)

        DB::statement('PRAGMA foreign_keys = OFF');

        // Respaldar datos existentes si los hay
        $existingPayments = DB::table('purchase_payments')->get();

        // Eliminar tabla completamente
        DB::statement('DROP TABLE IF EXISTS purchase_payments');

        // Recrear tabla con la estructura correcta (sin ENUM, usando STRING)
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

        // Restaurar datos, mapeando 'externo' → 'efectivo_personal'
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
        }

        DB::statement('PRAGMA foreign_keys = ON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No podemos revertir fácilmente porque implicaría volver a ENUM
        // y perder datos si alguien usó 'efectivo_personal'
        throw new \RuntimeException('This migration cannot be reversed safely.');
    }
};
