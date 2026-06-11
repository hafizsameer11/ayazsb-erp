<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weaving_departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('weaving_looms', function (Blueprint $table) {
            $table->id();
            $table->string('loom_no', 40)->unique();
            $table->string('name')->nullable();
            $table->string('loom_type', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('weaving_sets', function (Blueprint $table) {
            $table->id();
            $table->string('set_no', 40)->unique();
            $table->string('company_set_no', 40)->nullable();
            $table->date('entry_date')->nullable();
            $table->date('receipt_date')->nullable();
            $table->foreignId('sizing_party_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('grey_conversion_contract_id')->nullable()->constrained('grey_conversion_contracts')->nullOnDelete();
            $table->foreignId('grey_quality_id')->nullable()->constrained('grey_qualities')->nullOnDelete();
            $table->decimal('shrink_percent', 8, 4)->default(0);
            $table->decimal('width', 10, 4)->nullable();
            $table->decimal('ends_tareen', 12, 4)->nullable();
            $table->decimal('meters', 14, 4)->default(0);
            $table->json('meta')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('weaving_beams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weaving_set_id')->constrained('weaving_sets')->cascadeOnDelete();
            $table->string('beam_no', 40);
            $table->decimal('beam_length', 14, 4)->default(0);
            $table->string('status', 20)->default('available');
            $table->foreignId('loom_id')->nullable()->constrained('weaving_looms')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['weaving_set_id', 'beam_no']);
        });

        Schema::create('weaving_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('screen_slug', 80);
            $table->string('trans_no', 40);
            $table->date('trans_date');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('weaving_departments')->nullOnDelete();
            $table->foreignId('source_transaction_id')->nullable()->constrained('weaving_transactions')->nullOnDelete();
            $table->foreignId('weaving_set_id')->nullable()->constrained('weaving_sets')->nullOnDelete();
            $table->foreignId('grey_conversion_contract_id')->nullable()->constrained('grey_conversion_contracts')->nullOnDelete();
            $table->foreignId('grey_quality_id')->nullable()->constrained('grey_qualities')->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->text('remarks')->nullable();
            $table->json('meta')->nullable();
            $table->decimal('total_qty', 16, 4)->default(0);
            $table->decimal('total_amount', 16, 4)->default(0);
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['screen_slug', 'trans_no']);
        });

        Schema::create('weaving_transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weaving_transaction_id')->constrained('weaving_transactions')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->unsignedSmallInteger('line_no')->default(0);
            $table->string('description')->nullable();
            $table->decimal('qty', 16, 4)->default(0);
            $table->decimal('rate', 16, 4)->default(0);
            $table->decimal('amount', 16, 4)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('weaving_stock_balances', function (Blueprint $table) {
            $table->id();
            $table->string('stock_pool', 20);
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('qty', 16, 4)->default(0);
            $table->timestamps();
            $table->unique(['stock_pool', 'item_id']);
        });

        Schema::create('weaving_production_entries', function (Blueprint $table) {
            $table->id();
            $table->string('doc_no', 40)->unique();
            $table->date('doc_date');
            $table->foreignId('contract_grey_quality_id')->nullable()->constrained('grey_qualities')->nullOnDelete();
            $table->foreignId('production_grey_quality_id')->nullable()->constrained('grey_qualities')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('weaving_production_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weaving_production_entry_id')->constrained('weaving_production_entries')->cascadeOnDelete();
            $table->unsignedSmallInteger('sr')->default(0);
            $table->foreignId('loom_id')->nullable()->constrained('weaving_looms')->nullOnDelete();
            $table->foreignId('beam_id')->nullable()->constrained('weaving_beams')->nullOnDelete();
            $table->foreignId('weaving_set_id')->nullable()->constrained('weaving_sets')->nullOnDelete();
            $table->foreignId('grey_conversion_contract_id')->nullable()->constrained('grey_conversion_contracts')->nullOnDelete();
            $table->foreignId('grey_quality_id')->nullable()->constrained('grey_qualities')->nullOnDelete();
            $table->decimal('width', 10, 4)->nullable();
            $table->decimal('beam_balance', 14, 4)->nullable();
            $table->json('sides')->nullable();
            $table->string('beam_status', 40)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('weaving_piece_lengths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weaving_production_line_id')->constrained('weaving_production_lines')->cascadeOnDelete();
            $table->decimal('length', 14, 4)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weaving_piece_lengths');
        Schema::dropIfExists('weaving_production_lines');
        Schema::dropIfExists('weaving_production_entries');
        Schema::dropIfExists('weaving_stock_balances');
        Schema::dropIfExists('weaving_transaction_lines');
        Schema::dropIfExists('weaving_transactions');
        Schema::dropIfExists('weaving_beams');
        Schema::dropIfExists('weaving_sets');
        Schema::dropIfExists('weaving_looms');
        Schema::dropIfExists('weaving_departments');
    }
};
