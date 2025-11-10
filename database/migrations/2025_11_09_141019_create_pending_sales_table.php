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
        Schema::create('pending_sales', function (Blueprint $table) {
            $table->id();
            
            // Usuario que guardó la venta
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Turno de caja actual
            $table->foreignId('cash_shift_id')->nullable()->constrained()->onDelete('set null');
            
            // Los productos (guardados como JSON)
            $table->json('items');
            
            // Información adicional
            $table->string('customer_name', 100)->nullable();
            $table->text('notes')->nullable();
            
            // Totales
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            // Fechas de creación y actualización
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_sales');
    }
};