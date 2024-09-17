<?php

use App\Enums\Room\RoomTypeEnum;
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
        Schema::table('rooms', function (Blueprint $table) {
            $table->addColumn('integer', 'type')->after('current_question_end_at')->default(RoomTypeEnum::KAHOOT->value);
        });
        Schema::table('gamer_answers', function (Blueprint $table) {
            $table->addColumn('integer', 'type')->after('score')->default(RoomTypeEnum::KAHOOT->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('gamer_answers', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
