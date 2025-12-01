<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First clear existing timetables that reference time slots
        DB::table('timetables')->delete();

        // Then clear existing time slots
        DB::table('time_slots')->delete();

        // Add missing columns if they don't exist
        Schema::table('time_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('time_slots', 'slot_type')) {
                $table->enum('slot_type', ['regular', 'break', 'additional'])->default('regular')->after('slot_number');
            }
            if (!Schema::hasColumn('time_slots', 'day_of_week')) {
                $table->tinyInteger('day_of_week')->nullable()->comment('1=Monday, 2=Tuesday, etc. NULL for all days')->after('slot_type');
            }
            if (!Schema::hasColumn('time_slots', 'period_number')) {
                $table->tinyInteger('period_number')->nullable()->comment('Period number (1-8), NULL for breaks')->after('day_of_week');
            }
            if (!Schema::hasColumn('time_slots', 'description')) {
                $table->text('description')->nullable()->after('period_number');
            }
            if (!Schema::hasColumn('time_slots', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
            }
        });

        // Add indexes for better performance (check if they don't exist)
        $indexes = DB::select("SHOW INDEX FROM time_slots");
        $existingIndexes = array_column($indexes, 'Key_name');

        Schema::table('time_slots', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('time_slots_start_time_end_time_index', $existingIndexes)) {
                $table->index(['start_time', 'end_time']);
            }
            if (!in_array('time_slots_slot_type_index', $existingIndexes)) {
                $table->index('slot_type');
            }
            if (!in_array('time_slots_day_of_week_index', $existingIndexes)) {
                $table->index('day_of_week');
            }
            if (!in_array('time_slots_slot_type_status_index', $existingIndexes)) {
                $table->index(['slot_type', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropIndex(['start_time', 'end_time']);
            $table->dropIndex(['slot_type']);
            $table->dropIndex(['day_of_week']);
            $table->dropIndex(['slot_type', 'status']);

            if (Schema::hasColumn('time_slots', 'slot_type')) {
                $table->dropColumn('slot_type');
            }
            if (Schema::hasColumn('time_slots', 'day_of_week')) {
                $table->dropColumn('day_of_week');
            }
            if (Schema::hasColumn('time_slots', 'period_number')) {
                $table->dropColumn('period_number');
            }
            if (Schema::hasColumn('time_slots', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('time_slots', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
