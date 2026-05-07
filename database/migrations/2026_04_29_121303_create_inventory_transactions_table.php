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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('screen_slug');
            $table->string('trans_no')->index();
            $table->date('trans_date');
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('from_godown_id')->nullable()->constrained('godowns')->nullOnDelete();
            $table->foreignId('to_godown_id')->nullable()->constrained('godowns')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('remarks')->nullable();
            $table->decimal('total_qty', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['module', 'screen_slug', 'trans_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
