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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role', 'type');
            $table->tinyInteger('type')->change();
            $table->string('avatar')->nullable()->after('type');
            $table->string('google_id')->nullable()->after('email');
            $table->boolean('disabled')->default(false)->after('avatar');
            $table->timestamp('disabled_at')->nullable()->after('disabled');
            $table->boolean('super_admin')->default(false)->after('disabled_at');
            $table->timestamp('latest_login')->nullable()->after('type');
            $table->string('latest_ip_login')->nullable()->after('latest_login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('type', 'role');
            $table->string('role')->change();
            $table->dropColumn('avatar');
            $table->dropColumn('google_id');
            $table->dropColumn('disabled');
            $table->dropColumn('disabled_at');
            $table->dropColumn('super_admin');
            $table->dropColumn('latest_login');
            $table->dropColumn('latest_ip_login');
        });
    }
};
