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
            $table->longText('title')->change();
            $table->longText('content_html')->nullable()->after('is_old_question');
            $table->string('image')->nullable()->after('content_html');
            $table->tinyInteger('type')->default(0)->after('image')->comment('0: văn bản thường, 1: có thêm ảnh');
            $table->integer('time_reply')->default(config('app.quizzes.time_reply'))->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('title')->change();
            $table->dropColumn(['content_html', 'image', 'type', 'time_reply']);
        });
    }
};
