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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            
            // Información básica del movimiento
            $table->date('date');                           // Fecha del movimiento
            $table->enum('type', ['ingreso', 'egreso']);    // Tipo: ingreso o egreso
            
            // Categorización
            $table->string('category')->nullable();          // Categoría predefinida
            $table->string('custom_category')->nullable();   // Categoría personalizada (si escribe "Otro")
            
            // Detalles
            $table->text('description');                     // Descripción del movimiento
            $table->decimal('amount', 10, 2);               // Monto (hasta 99,999,999.99)
            $table->enum('payment_method', [
                'efectivo',
                'transferencia',
                'tarjeta',
                'otro'
            ]);                                              // Método de pago
            
            // Archivos y notas
            $table->string('receipt_file')->nullable();      // Ruta del comprobante (PDF/imagen)
            $table->text('notes')->nullable();               // Notas adicionales
            
            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable(); // Usuario que lo registró
            $table->timestamps();                            // created_at, updated_at
            
            // Índices para búsquedas rápidas
            $table->index('date');                           // Buscar por fecha
            $table->index('type');                           // Filtrar por tipo
            $table->index('category');                       // Filtrar por categoría
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};