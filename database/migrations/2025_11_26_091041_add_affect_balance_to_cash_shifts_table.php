<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cash_shifts', function (Blueprint $table) {
            // Campo para indicar si el faltante/sobrante afecta el balance
            $table->boolean('affect_balance')->default(true)->after('difference');
        });
    }

    public function down()
    {
        Schema::table('cash_shifts', function (Blueprint $table) {
            $table->dropColumn('affect_balance');
        });
    }
};