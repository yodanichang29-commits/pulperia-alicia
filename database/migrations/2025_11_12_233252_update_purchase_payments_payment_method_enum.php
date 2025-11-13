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
        // Actualizar valores 'externo' existentes a 'efectivo_personal'
        // Nota: En SQLite los enums son solo strings, así que podemos actualizar directamente
        DB::table('purchase_payments')
            ->where('payment_method', 'externo')
            ->update(['payment_method' => 'efectivo_personal']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios
        DB::table('purchase_payments')
            ->where('payment_method', 'efectivo_personal')
            ->update(['payment_method' => 'externo']);
    }
};
