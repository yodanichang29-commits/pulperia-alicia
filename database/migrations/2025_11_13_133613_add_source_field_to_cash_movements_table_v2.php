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
        // Solo agregar la columna si no existe
        if (!Schema::hasColumn('cash_movements', 'source')) {
            Schema::table('cash_movements', function (Blueprint $table) {
                $table->string('source', 10)
                      ->nullable()
                      ->default('caja')
                      ->after('payment_method')
                      ->comment('Origen del dinero: caja o externo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cash_movements', 'source')) {
            Schema::table('cash_movements', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
};
