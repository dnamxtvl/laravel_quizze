<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_forgot_password_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->fulltext();
            $table->string('ip')->nullable();
            $table->string('device')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('country_name')->nullable()->fulltext();
            $table->string('city_name')->nullable()->fulltext();
            $table->tinyInteger('type')->index();
            $table->index(['user_id', 'type']);
            $table->index(['country_name', 'city_name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_forgot_password_logs');
    }
};
