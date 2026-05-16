<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yarn_contracts', function (Blueprint $table) {
            $table->string('contract_code')->nullable()->after('contract_no');
            $table->string('payment_term')->default('cash')->after('contract_type');
            $table->foreignId('broker_account_id')->nullable()->after('account_id')->constrained('accounts')->nullOnDelete();
            $table->decimal('commission_percent', 8, 4)->default(0)->after('broker_account_id');
            $table->decimal('brokery_percent', 8, 4)->default(0)->after('commission_percent');
            $table->string('yarn_type')->default('any')->after('brokery_percent');
            $table->decimal('no_of_cones', 18, 4)->default(0)->after('quantity');
            $table->decimal('total_kgs', 18, 4)->default(0)->after('weight_lbs');
            $table->decimal('total_amount', 18, 2)->default(0)->after('total_kgs');
            $table->decimal('total_commission', 18, 2)->default(0)->after('total_amount');
            $table->decimal('total_brokery', 18, 2)->default(0)->after('total_commission');
            $table->decimal('total_net_amount', 18, 2)->default(0)->after('total_brokery');
        });
    }

    public function down(): void
    {
        Schema::table('yarn_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('broker_account_id');
            $table->dropColumn([
                'contract_code',
                'payment_term',
                'commission_percent',
                'brokery_percent',
                'yarn_type',
                'no_of_cones',
                'total_kgs',
                'total_amount',
                'total_commission',
                'total_brokery',
                'total_net_amount',
            ]);
        });
    }
};
