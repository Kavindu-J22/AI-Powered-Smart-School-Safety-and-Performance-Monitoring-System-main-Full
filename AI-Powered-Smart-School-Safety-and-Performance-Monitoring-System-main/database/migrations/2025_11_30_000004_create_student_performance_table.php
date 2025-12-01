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
        Schema::create('student_performance', function (Blueprint $table) {
            $table->id('performance_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects', 'id')->onDelete('cascade');
            $table->integer('grade_level');
            $table->string('academic_year', 9);
            $table->integer('term')->nullable();
            $table->integer('month')->nullable();

            // Aggregated metrics
            $table->integer('total_homework_assigned')->default(0);
            $table->integer('homework_completed')->default(0);
            $table->integer('homework_on_time')->default(0);
            $table->decimal('average_score', 5, 2)->nullable();
            $table->decimal('highest_score', 5, 2)->nullable();
            $table->decimal('lowest_score', 5, 2)->nullable();
            $table->string('grade', 5)->nullable();

            // Performance breakdown by question type
            $table->decimal('mcq_average', 5, 2)->nullable();
            $table->decimal('short_answer_average', 5, 2)->nullable();
            $table->decimal('descriptive_average', 5, 2)->nullable();

            // Trend analysis
            $table->enum('trend', ['improving', 'stable', 'declining', 'needs_attention'])->nullable();
            $table->json('strong_areas')->nullable();
            $table->json('weak_areas')->nullable();
            $table->json('recommendations')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'academic_year', 'month'], 'student_perf_unique');
            $table->index(['student_id', 'academic_year'], 'student_perf_student_year');
            $table->index(['subject_id', 'grade_level'], 'student_perf_subject_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_performance');
    }
};
