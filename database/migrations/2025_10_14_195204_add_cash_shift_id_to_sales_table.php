<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('sales')) return;

        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'cash_shift_id')) {
                $table->foreignId('cash_shift_id')
                    ->nullable()
                    ->constrained('cash_shifts')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sales')) return;

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'cash_shift_id')) {
                try { $table->dropConstrainedForeignId('cash_shift_id'); } catch (\Throwable $e) {}
                try { $table->dropColumn('cash_shift_id'); } catch (\Throwable $e) {}
            }
        });
    }
};
