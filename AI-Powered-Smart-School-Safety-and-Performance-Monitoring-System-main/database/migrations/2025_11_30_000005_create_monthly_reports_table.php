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
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->integer('grade_level');
            $table->string('academic_year', 9);
            $table->integer('month');
            $table->integer('year');

            // Overall performance
            $table->decimal('overall_average', 5, 2)->nullable();
            $table->string('overall_grade', 5)->nullable();
            $table->integer('class_rank')->nullable();
            $table->integer('total_students_in_class')->nullable();

            // Subject-wise performance (stored as JSON)
            $table->json('subject_performance')->nullable();

            // Analysis
            $table->json('strengths')->nullable();
            $table->json('areas_for_improvement')->nullable();
            $table->json('recommendations')->nullable();

            // Homework statistics
            $table->integer('total_homework_assigned')->default(0);
            $table->integer('homework_completed')->default(0);
            $table->integer('homework_on_time')->default(0);
            $table->decimal('completion_rate', 5, 2)->nullable();

            // Report metadata
            $table->enum('status', ['generated', 'reviewed', 'sent_to_parents', 'acknowledged'])->default('generated');
            $table->timestamp('sent_to_parents_at')->nullable();
            $table->timestamp('parent_acknowledged_at')->nullable();
            $table->string('report_file_path')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'year', 'month'], 'monthly_reports_unique');
            $table->index(['year', 'month', 'status'], 'monthly_reports_period_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
