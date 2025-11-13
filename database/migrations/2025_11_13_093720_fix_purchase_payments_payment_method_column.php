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

        // Renombrar tabla existente
        Schema::rename('purchase_payments', 'purchase_payments_old');

        // Crear nueva tabla con la estructura correcta
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('inventory_transactions')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method'); // STRING en lugar de ENUM
            // Valores permitidos: 'caja', 'efectivo_personal', 'credito', 'transferencia', 'tarjeta'
            $table->boolean('affects_cash')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Copiar datos existentes (si los hay), mapeando 'externo' → 'efectivo_personal'
        DB::statement("
            INSERT INTO purchase_payments (id, purchase_id, amount, payment_method, affects_cash, notes, user_id, created_at, updated_at)
            SELECT
                id,
                purchase_id,
                amount,
                CASE
                    WHEN payment_method = 'externo' THEN 'efectivo_personal'
                    ELSE payment_method
                END as payment_method,
                affects_cash,
                notes,
                user_id,
                created_at,
                updated_at
            FROM purchase_payments_old
        ");

        // Eliminar tabla vieja
        Schema::drop('purchase_payments_old');

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
