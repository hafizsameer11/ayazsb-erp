<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yarn_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_no');
            $table->string('direction');
            $table->string('contract_type')->default('BY RATE');
            $table->date('contract_date');
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('godown_id')->nullable()->constrained('godowns')->nullOnDelete();
            $table->string('yarn_tag')->nullable();
            $table->string('condition')->nullable();
            $table->string('unit')->default('LBS');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('weight_lbs', 18, 4)->default(0);
            $table->decimal('packing_size', 18, 4)->default(0);
            $table->decimal('packing_weight', 18, 4)->default(0);
            $table->decimal('rate', 18, 4)->default(0);
            $table->decimal('sale_rate', 18, 4)->nullable();
            $table->string('status')->default('open');
            $table->string('remarks')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['contract_no', 'direction']);
            $table->index(['party_id', 'contract_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yarn_contracts');
    }
};
