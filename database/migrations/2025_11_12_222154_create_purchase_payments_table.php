<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('inventory_transactions')->cascadeOnDelete();
            $table->decimal('amount', 12, 2); // Monto del pago
            $table->string('payment_method'); // Usamos string en lugar de enum para SQLite
            // Valores permitidos: 'caja', 'efectivo_personal', 'credito', 'transferencia', 'tarjeta'
            $table->boolean('affects_cash')->default(false); // ¿Afecta la caja del turno?
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
