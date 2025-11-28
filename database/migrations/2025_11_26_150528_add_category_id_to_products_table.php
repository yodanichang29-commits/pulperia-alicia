<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::table('products', function (Blueprint $table) {
        // Agregar columna category_id sin especificar after
        // Se agregará al final de la tabla
        $table->foreignId('category_id')
              ->nullable()
              ->constrained('categories')
              ->onDelete('set null');
    });
}

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};