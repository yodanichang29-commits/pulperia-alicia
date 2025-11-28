<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nombre de la categoría
            $table->text('description')->nullable(); // Descripción opcional
            $table->boolean('active')->default(true); // Si está activa o no
            $table->integer('order')->default(0); // Orden de aparición
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};