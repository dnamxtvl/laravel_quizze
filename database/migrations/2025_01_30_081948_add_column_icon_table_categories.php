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
        Schema::table('category', function (Blueprint $table) {
            $table->string('icon')->after('name');
            $table->string('color_icon')->after('icon');
            $table->string('bg_icon')->after('color_icon');
            $table->json('music')->after('bg_icon');
            $table->json('background')->after('music');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category', function (Blueprint $table) {
            $table->dropColumn(['icon', 'color_icon', 'bg_icon', 'music', 'background']);
        });
    }
};
