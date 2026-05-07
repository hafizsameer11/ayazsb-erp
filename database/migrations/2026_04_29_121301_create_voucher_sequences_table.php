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
        Schema::create('voucher_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('voucher_type');
            $table->string('year_code');
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
            $table->unique(['module', 'voucher_type', 'year_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_sequences');
    }
};
