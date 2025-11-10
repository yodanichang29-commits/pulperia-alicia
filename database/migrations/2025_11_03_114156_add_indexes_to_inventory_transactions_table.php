<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Índice simple (si solo quieres por provider_id)
            // $table->index('provider_id', 'tx_provider_idx');

            // RECOMENDADO: índice compuesto para tu consulta actual
            // (filtras por provider_id y ordenas por moved_at)
            $table->index(['provider_id', 'moved_at'], 'tx_provider_moved_idx');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Si usaste el simple:
            // $table->dropIndex('tx_provider_idx');

            // Si usaste el compuesto:
            $table->dropIndex('tx_provider_moved_idx');
        });
    }
};