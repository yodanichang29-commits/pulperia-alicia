<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();

            // Cliente
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // Usuario que cobró
            $table->foreignId('user_id')->constrained('users');

            // Turno de caja (puede ser null si cobraste fuera de turno, pero lo validaremos en backend)
            $table->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts')->nullOnDelete();

            // Datos del abono
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['efectivo', 'tarjeta', 'transferencia']);
            $table->string('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_payments');
    }
};
