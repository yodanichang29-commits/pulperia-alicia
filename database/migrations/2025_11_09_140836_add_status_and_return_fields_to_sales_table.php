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
        Schema::table('sales', function (Blueprint $table) {
            // Estado de la venta (completed, pending, voided, returned)
            $table->string('status', 20)->default('completed')->after('total');
            
            // Si es devolución, guarda la venta original
            $table->unsignedBigInteger('original_sale_id')->nullable()->after('status');
            
            // Razón de la devolución
            $table->text('return_reason')->nullable()->after('original_sale_id');
            
            // Crear la relación con sales (para devoluciones)
            $table->foreign('original_sale_id')
                  ->references('id')
                  ->on('sales')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['original_sale_id']);
            $table->dropColumn(['status', 'original_sale_id', 'return_reason']);
        });
    }
};