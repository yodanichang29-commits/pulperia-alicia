<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->decimal('opening_float', 12, 2);
            $table->timestamp('closed_at')->nullable();
            $table->decimal('closing_cash_count', 12, 2)->nullable();
            $table->decimal('expected_cash', 12, 2)->nullable();   // calculado al cerrar
            $table->decimal('difference', 12, 2)->nullable();       // closing - expected
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');
    }
};
