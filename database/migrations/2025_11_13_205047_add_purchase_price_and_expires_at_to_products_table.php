<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Costo de compra
            $table->decimal('purchase_price', 10, 2)
                  ->nullable()
                  ->after('price');

            // Fecha de expiración
            $table->date('expires_at')
                  ->nullable()
                  ->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'expires_at']);
        });
    }
};
