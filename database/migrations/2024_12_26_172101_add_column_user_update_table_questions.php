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
        Schema::table('questions', function (Blueprint $table) {
//            $table->boolean('created_by_sys')->after('quizze_id')->default(false);
//            $table->uuid('updated_by')->after('is_old_question')->index()->nullable();
//            $table->boolean('updated_by_sys')->after('updated_by')->default(false);
        });
        Schema::table('quizzes', function (Blueprint $table) {
//            $table->string('code', 10)->after('title')->unique();
//            $table->boolean('created_by_sys')->after('user_id')->default(false);
//            $table->uuid('deleted_by')->after('created_by_sys')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('updated_by');
            $table->dropColumn('updated_by_sys');
            $table->dropColumn('created_by_sys');
        });
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('created_by_sys');
            $table->dropColumn('deleted_by');
        });
    }
};
