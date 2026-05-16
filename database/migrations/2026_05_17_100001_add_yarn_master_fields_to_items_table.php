<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('yarn_count_id')->nullable()->after('module')->constrained('yarn_counts')->nullOnDelete();
            $table->foreignId('yarn_thread_id')->nullable()->after('yarn_count_id')->constrained('yarn_threads')->nullOnDelete();
            $table->foreignId('yarn_blend_id')->nullable()->after('yarn_thread_id')->constrained('yarn_blends')->nullOnDelete();
            $table->foreignId('yarn_brand_id')->nullable()->after('yarn_blend_id')->constrained('yarn_brands')->nullOnDelete();
            $table->foreignId('yarn_ratio_id')->nullable()->after('yarn_brand_id')->constrained('yarn_ratios')->nullOnDelete();
            $table->string('item_type')->nullable()->after('yarn_ratio_id');
            $table->unsignedInteger('pack_size_cones')->nullable()->after('item_type');
            $table->decimal('packing_weight', 18, 4)->nullable()->after('pack_size_cones');
            $table->string('yarn_code')->nullable()->after('packing_weight');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('yarn_count_id');
            $table->dropConstrainedForeignId('yarn_thread_id');
            $table->dropConstrainedForeignId('yarn_blend_id');
            $table->dropConstrainedForeignId('yarn_brand_id');
            $table->dropConstrainedForeignId('yarn_ratio_id');
            $table->dropColumn(['item_type', 'pack_size_cones', 'packing_weight', 'yarn_code']);
        });
    }
};
