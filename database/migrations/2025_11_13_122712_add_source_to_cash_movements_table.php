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
            // Usar string en lugar de enum para mejor compatibilidad con SQLite
            $table->string('source', 10)
                  ->nullable()
                  ->default('caja')
                  ->after('payment_method')
                  ->comment('Origen del dinero: caja (negocio) o externo (personal)');
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
