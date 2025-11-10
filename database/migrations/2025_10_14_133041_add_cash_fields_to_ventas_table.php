<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agrega columnas si NO existen (evita choques si ya las tenías)
        Schema::table('sales', function (Blueprint $table) {
    if (!Schema::hasColumn('sales', 'cash_received')) {
        $table->decimal('cash_received', 10, 2)->nullable()->after('total');
    }
    if (!Schema::hasColumn('sales', 'cash_change')) {
        $table->decimal('cash_change', 10, 2)->nullable()->after('cash_received');
    }
    if (!Schema::hasColumn('sales', 'fee_pct')) {
        $table->decimal('fee_pct', 5, 2)->default(0)->after('tipo_pago');
    }
    if (!Schema::hasColumn('sales', 'surcharge')) {
        $table->decimal('surcharge', 10, 2)->default(0)->after('fee_pct');
    }
});

    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
    if (Schema::hasColumn('sales', 'cash_received')) $table->dropColumn('cash_received');
    if (Schema::hasColumn('sales', 'cash_change'))   $table->dropColumn('cash_change');
    if (Schema::hasColumn('sales', 'fee_pct'))       $table->dropColumn('fee_pct');
    if (Schema::hasColumn('sales', 'surcharge'))     $table->dropColumn('surcharge');
});

    }
};
