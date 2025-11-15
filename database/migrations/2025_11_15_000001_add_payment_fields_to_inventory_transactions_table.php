<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Agrega campos para registrar pagos divididos en compras:
     * - paid_from_cash: monto pagado desde la caja del turno
     * - paid_from_outside: monto pagado desde fondos externos (banco, dueño, etc.)
     */
    public function up(): void {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Monto pagado desde la caja del turno actual
            $table->decimal('paid_from_cash', 10, 2)
                  ->default(0)
                  ->after('total_cost')
                  ->comment('Monto pagado desde la caja del turno');

            // Monto pagado desde fondos externos (banco, dueño, etc.)
            $table->decimal('paid_from_outside', 10, 2)
                  ->default(0)
                  ->after('paid_from_cash')
                  ->comment('Monto pagado desde fondos externos (banco, dueño, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn(['paid_from_cash', 'paid_from_outside']);
        });
    }
};
