<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yarn_contracts', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('party_id')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('party_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('from_account_id')->nullable()->after('account_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('to_account_id')->nullable()->after('from_account_id')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('to_account_id');
            $table->dropConstrainedForeignId('from_account_id');
            $table->dropConstrainedForeignId('account_id');
        });

        Schema::table('yarn_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
        });
    }
};
