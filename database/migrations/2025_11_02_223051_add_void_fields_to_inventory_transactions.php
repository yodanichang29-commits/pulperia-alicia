<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('moved_at');
            $table->foreignId('voided_by')->nullable()->constrained('users');
        });
    }
    public function down(): void {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn('voided_at');
        });
    }
};
