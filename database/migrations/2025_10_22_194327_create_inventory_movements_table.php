<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // in: entrada | out: salida por venta | adjust: ajuste manual (puede sumar o restar)
            $table->enum('type', ['in','out','adjust']);
            $table->integer('qty'); // siempre POSITIVA si type=in/out. En adjust puede ser positiva o negativa.
            $table->integer('before_qty')->default(0);
            $table->integer('after_qty')->default(0);
            $table->string('reason')->nullable(); // opcional
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory_movements');
    }
};
