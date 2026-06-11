<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weaving_account_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_stock_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('yarn_stock_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('grey_stock_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('default_expense_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('sizing_expense_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('fabric_sales_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('fabric_cogs_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('weaving_sets', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->after('created_by')->constrained('vouchers')->nullOnDelete();
        });

        Schema::table('weaving_departments', function (Blueprint $table) {
            $table->foreignId('expense_account_id')->nullable()->after('name')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('weaving_departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_account_id');
        });

        Schema::table('weaving_sets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voucher_id');
        });

        Schema::dropIfExists('weaving_account_settings');
    }
};
