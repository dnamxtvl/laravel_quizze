<?php

use App\Enums\Room\RoomStatusEnum;
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
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quizze_id')->index();
            $table->integer('code')->index();
            $table->tinyInteger('status')->default(RoomStatusEnum::PREPARE);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->uuid('current_question_id')->index()->nullable();
            $table->timestamp('current_question_start_at')->nullable();
            $table->timestamp('current_question_end_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
