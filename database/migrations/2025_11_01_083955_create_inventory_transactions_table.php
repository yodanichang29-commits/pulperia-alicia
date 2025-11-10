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
    Schema::create('inventory_transactions', function (Blueprint $table) {
        $table->id();
        // 'in' = entrada (compra/ajuste+), 'out' = salida (merma/dañado/vencido/uso interno/ajuste-)
        $table->enum('type', ['in','out']);
        // Motivo sencillo, sin enredarnos
        $table->enum('reason', [
            'purchase','adjust_in','waste','damaged','expired','internal_use','adjust_out'
        ])->default('purchase');

        $table->date('moved_at')->nullable();       // fecha del documento
        $table->string('supplier')->nullable();     // nombre proveedor (texto)
        $table->string('reference')->nullable();    // factura/nota
        $table->text('notes')->nullable();          // comentario libre

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->decimal('total_cost', 12, 2)->default(0); // suma de sus ítems
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('inventory_transactions');
}

};
