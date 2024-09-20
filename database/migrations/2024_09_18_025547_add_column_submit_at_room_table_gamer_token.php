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
        Schema::table('gamer_token', function (Blueprint $table) {
            $table->addColumn('timestamp', 'submit_at')->after('expired_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gamer_token', function (Blueprint $table) {
            $table->dropColumn('submit_at');
        });
    }
};
