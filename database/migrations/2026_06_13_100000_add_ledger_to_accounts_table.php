<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('ledger', 20)->default('general')->after('id');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['ledger', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['ledger', 'code']);
            $table->unique('code');
            $table->dropColumn('ledger');
        });
    }
};
