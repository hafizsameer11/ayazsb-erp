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
        Schema::table('voucher_lines', function (Blueprint $table) {
            $table
                ->foreign('voucher_id')
                ->references('id')
                ->on('vouchers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voucher_lines', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
        });
    }
};
