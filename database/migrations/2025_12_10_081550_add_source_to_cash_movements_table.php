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
        Schema::table('cash_movements', function (Blueprint $table) {
            // Agregar campo 'source' después de 'payment_method'
            // 'fondo' = agregar al fondo (NO afecta finanzas)
            // 'caja_turno' = gasto operativo (SÍ afecta finanzas)
            $table->enum('source', ['fondo', 'caja_turno'])
                  ->default('caja_turno')
                  ->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};