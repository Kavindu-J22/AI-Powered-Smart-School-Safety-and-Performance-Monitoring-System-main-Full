<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->time('checkin_deadline')->default('08:00:00')->after('school_end_time');
            $table->time('checkout_time')->default('15:00:00')->after('checkin_deadline');
            $table->unsignedSmallInteger('late_after_minutes')->default(15)->after('checkout_time');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['checkin_deadline', 'checkout_time', 'late_after_minutes']);
        });
    }
};
