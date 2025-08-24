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
        Schema::create('quizze_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('quizze_id')->index();
            $table->uuid('last_updated_by')->index();
            $table->integer('speed_priority')->default(100);
            $table->string('background')->nullable();
            $table->string('background_name')->nullable();
            $table->string('music')->nullable();
            $table->string('music_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizze_settings');
    }
};
