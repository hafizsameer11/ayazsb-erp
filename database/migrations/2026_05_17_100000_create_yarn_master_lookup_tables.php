<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yarn_counts', function (Blueprint $table) {
            $table->id();
            $table->string('count');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('yarn_threads', function (Blueprint $table) {
            $table->id();
            $table->string('thread');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('yarn_blends', function (Blueprint $table) {
            $table->id();
            $table->string('blend');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('yarn_brands', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('yarn_ratios', function (Blueprint $table) {
            $table->id();
            $table->string('ratio');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yarn_ratios');
        Schema::dropIfExists('yarn_brands');
        Schema::dropIfExists('yarn_blends');
        Schema::dropIfExists('yarn_threads');
        Schema::dropIfExists('yarn_counts');
    }
};
