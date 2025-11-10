<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->date('moved_at')->nullable()->after('user_id');                // fecha del movimiento
            $table->string('supplier')->nullable()->after('moved_at');             // proveedor (texto)
            $table->string('reference')->nullable()->after('supplier');            // # factura/nota
            $table->decimal('unit_cost', 10, 2)->nullable()->after('reference');   // costo unitario (solo entradas)
            $table->decimal('total_cost', 12, 2)->nullable()->after('unit_cost');  // qty * unit_cost
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['moved_at','supplier','reference','unit_cost','total_cost']);
        });
    }
};
