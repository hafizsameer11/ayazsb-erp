<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yarn_account_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yarn_stock_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('yarn_sales_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yarn_account_settings');
    }
};
