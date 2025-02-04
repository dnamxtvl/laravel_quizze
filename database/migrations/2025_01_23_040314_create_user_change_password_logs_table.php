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
        Schema::create('user_change_password_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->index();
            $table->string('ip');
            $table->string('user_agent');
            $table->string('old_password');
            $table->string('new_password');
            $table->uuid('change_by')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_change_password_logs');
    }
};
