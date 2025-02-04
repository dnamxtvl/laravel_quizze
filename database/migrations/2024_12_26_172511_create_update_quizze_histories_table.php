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
        Schema::create('update_quizze_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('quizze_id')->index();
            $table->uuid('old_question_id')->nullable();
            $table->uuid('new_question_id')->nullable();
            $table->uuid('updated_by')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_quizze_histories');
    }
};
