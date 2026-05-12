<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreignId('yarn_contract_id')->nullable()->after('party_id')->constrained('yarn_contracts')->nullOnDelete();
            $table->foreignId('from_yarn_contract_id')->nullable()->after('yarn_contract_id')->constrained('yarn_contracts')->nullOnDelete();
            $table->foreignId('to_yarn_contract_id')->nullable()->after('from_yarn_contract_id')->constrained('yarn_contracts')->nullOnDelete();
            $table->foreignId('source_transaction_id')->nullable()->after('to_yarn_contract_id')->constrained('inventory_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_transaction_id');
            $table->dropConstrainedForeignId('to_yarn_contract_id');
            $table->dropConstrainedForeignId('from_yarn_contract_id');
            $table->dropConstrainedForeignId('yarn_contract_id');
        });
    }
};
