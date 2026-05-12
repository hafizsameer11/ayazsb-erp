<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('remarks');
        });

        Schema::table('inventory_transaction_lines', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transaction_lines', function (Blueprint $table) {
            $table->dropColumn('meta');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
