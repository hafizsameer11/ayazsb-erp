<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grey_conversion_contracts', function (Blueprint $table) {
            $table->decimal('required_bags', 14, 4)->default(0)->after('qty_mtr');
        });
    }

    public function down(): void
    {
        Schema::table('grey_conversion_contracts', function (Blueprint $table) {
            $table->dropColumn('required_bags');
        });
    }
};
