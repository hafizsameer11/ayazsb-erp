<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_transaction_lines', function (Blueprint $table) {
            $table
                ->foreign('inventory_transaction_id')
                ->references('id')
                ->on('inventory_transactions')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transaction_lines', function (Blueprint $table) {
            $table->dropForeign(['inventory_transaction_id']);
        });
    }
};
