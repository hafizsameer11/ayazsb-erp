<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grey_qualities', function (Blueprint $table) {
            $table->id();
            $table->string('quality_no')->unique();
            $table->string('tag')->nullable();
            $table->string('season')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('reed', 10, 4)->nullable();
            $table->decimal('pick', 10, 4)->nullable();
            $table->decimal('width', 10, 4)->nullable();
            $table->decimal('total_ends', 14, 4)->default(0);
            $table->foreignId('yarn_blend_id')->nullable()->constrained('yarn_blends')->nullOnDelete();
            $table->string('blend_label')->nullable();
            $table->string('color')->nullable();
            $table->string('quality_name')->nullable();
            $table->string('quality_name_manual')->nullable();
            $table->string('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('grey_quality_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grey_quality_id')->constrained('grey_qualities')->cascadeOnDelete();
            $table->string('nature');
            $table->foreignId('yarn_count_id')->nullable()->constrained('yarn_counts')->nullOnDelete();
            $table->foreignId('yarn_thread_id')->nullable()->constrained('yarn_threads')->nullOnDelete();
            $table->foreignId('yarn_blend_id')->nullable()->constrained('yarn_blends')->nullOnDelete();
            $table->string('line_name')->nullable();
            $table->decimal('ends', 14, 4)->nullable();
            $table->decimal('picks', 14, 4)->nullable();
            $table->decimal('calc_count', 14, 4)->nullable();
            $table->decimal('weight', 18, 6)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('grey_conversion_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_no');
            $table->string('contract_code')->nullable();
            $table->string('contract_type')->default('CONV');
            $table->date('contract_date');
            $table->string('status')->default('running');
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('grey_quality_id')->nullable()->constrained('grey_qualities')->nullOnDelete();
            $table->string('nature')->nullable();
            $table->string('loom_type')->nullable();
            $table->decimal('loom_width', 12, 4)->nullable();
            $table->string('loom_panna')->nullable();
            $table->string('manual_quality_name')->nullable();
            $table->decimal('qty_mtr', 18, 4)->default(0);
            $table->decimal('conv_per_pick', 18, 6)->nullable();
            $table->decimal('per_mtr_rate', 18, 4)->default(0);
            $table->decimal('fabric_rate', 18, 4)->default(0);
            $table->unsignedInteger('looms_plan')->nullable();
            $table->date('completion_date')->nullable();
            $table->foreignId('invoice_quality_id')->nullable()->constrained('grey_qualities')->nullOnDelete();
            $table->foreignId('broker_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('brokery_type')->nullable();
            $table->decimal('brokery_rate', 12, 4)->default(0);
            $table->foreignId('checker_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('checker_rate', 12, 4)->default(0);
            $table->decimal('munshiana', 12, 4)->default(0);
            $table->decimal('commission_percent', 12, 4)->default(0);
            $table->string('freight_term')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('total_brokery', 18, 2)->default(0);
            $table->decimal('total_checkery', 18, 2)->default(0);
            $table->decimal('total_munshiana', 18, 2)->default(0);
            $table->decimal('total_net_amount', 18, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->json('warp_details')->nullable();
            $table->json('weft_details')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('contract_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grey_conversion_contracts');
        Schema::dropIfExists('grey_quality_details');
        Schema::dropIfExists('grey_qualities');
    }
};
