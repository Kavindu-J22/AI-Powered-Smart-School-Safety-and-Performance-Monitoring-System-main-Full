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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id('attendance_id');

            // Foreign key to students table
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('cascade');

            // Date and time information
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();

            // Status: present, late, absent, excused
            $table->enum('status', ['present', 'late', 'absent', 'excused'])->default('present');

            // NFC tag information
            $table->string('nfc_tag_id', 100)->nullable();

            // Location/device information
            $table->string('check_in_location', 100)->nullable();
            $table->string('check_out_location', 100)->nullable();
            $table->string('device_id', 50)->nullable(); // Which Arduino/reader

            // Temperature check (optional - for health screening)
            $table->decimal('temperature', 4, 1)->nullable();

            // Notes and remarks
            $table->text('remarks')->nullable();

            // Who recorded (for manual entries)
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->foreign('recorded_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Is it auto-recorded or manual?
            $table->boolean('is_auto_recorded')->default(true);

            $table->timestamps();

            // Indexes for better performance
            $table->index('student_id');
            $table->index('attendance_date');
            $table->index(['student_id', 'attendance_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
