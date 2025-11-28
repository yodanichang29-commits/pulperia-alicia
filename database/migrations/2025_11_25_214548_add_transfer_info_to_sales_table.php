<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('transfer_client_name')->nullable()->after('payment');
            $table->string('transfer_bank')->nullable()->after('transfer_client_name');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['transfer_client_name', 'transfer_bank']);
        });
    }
};