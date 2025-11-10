<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $t->decimal('qty', 12, 3);      // usa integer si solo manejas unidades enteras
            $t->decimal('price', 12, 2);    // precio unitario al momento
            $t->decimal('total', 12, 2);    // qty * price

            $t->timestamps();
            $t->index(['sale_id']);
            $t->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
