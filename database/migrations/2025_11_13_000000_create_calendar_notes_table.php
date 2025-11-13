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
        Schema::create('calendar_notes', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // Fecha del día
            $table->text('note')->nullable(); // Nota del usuario
            $table->enum('priority', ['low', 'normal', 'important', 'urgent'])->default('normal'); // Prioridad/Color
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que creó la nota
            $table->timestamps(); // created_at y updated_at para tracking

            // Índices para mejorar rendimiento
            $table->index('date');
            $table->index('user_id');
            $table->unique(['date', 'user_id']); // Un usuario solo puede tener una nota por día
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_notes');
    }
};
