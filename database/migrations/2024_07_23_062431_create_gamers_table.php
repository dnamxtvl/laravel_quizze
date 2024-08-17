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
        Schema::create('gamers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('socket_id')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('country_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('display_meme')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamers');
    }
};
