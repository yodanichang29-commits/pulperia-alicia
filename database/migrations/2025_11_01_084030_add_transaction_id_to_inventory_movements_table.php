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
    Schema::table('inventory_movements', function (Blueprint $table) {
        $table->foreignId('transaction_id')
              ->nullable()
              ->after('id')
              ->constrained('inventory_transactions')
              ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('inventory_movements', function (Blueprint $table) {
        $table->dropConstrainedForeignId('transaction_id');
    });
}

};
