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
//        Schema::table('rooms', function (Blueprint $table) {
//            $table->addColumn('integer', 'type')->after('current_question_end_at')->default(0);
//        });
//        Schema::table('gamer_answers', function (Blueprint $table) {
//            $table->addColumn('integer', 'type')->after('score')->default(0);
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
//        Schema::table('rooms', function (Blueprint $table) {
//            $table->dropColumn('type');
//        });
//        Schema::table('gamer_answers', function (Blueprint $table) {
//            $table->dropColumn('type');
//        });
    }
};
