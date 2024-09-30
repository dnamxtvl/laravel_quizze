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
        Schema::create('user_share_quizzes', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_share_id')->index();
            $table->uuid('quizze_id')->index();
            $table->uuid('receiver_id')->index();
            $table->index(['user_share_id', 'quizze_id']);
            $table->index(['quizze_id', 'receiver_id']);
            $table->string('token')->unique();
            $table->boolean('is_accept')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_share_quizzes');
    }
};
