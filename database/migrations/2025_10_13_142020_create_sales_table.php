<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();

            // SQLite no soporta enum: usamos string y validamos en la app
            $t->string('payment'); // 'cash','card','transfer','credit'

            // Totales
            $t->decimal('subtotal', 12, 2)->default(0);
            $t->decimal('surcharge', 12, 2)->default(0);
            $t->decimal('fee_pct', 5, 2)->default(0);  // %
            $t->decimal('total', 12, 2)->default(0);

            // Crédito
            $t->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $t->date('due_date')->nullable();

            // Efectivo (si ya los tienes en otra migración, puedes omitirlos aquí)
            $t->decimal('cash_received', 12, 2)->nullable();
            $t->decimal('cash_change', 12, 2)->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
