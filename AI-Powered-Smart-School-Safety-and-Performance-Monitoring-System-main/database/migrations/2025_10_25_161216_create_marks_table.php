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
        Schema::create('marks', function (Blueprint $table) {
            $table->id('mark_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects', 'id')->onDelete('cascade');
            $table->integer('grade_level'); // Grade level (1-13)
            $table->string('academic_year', 20); // e.g., '2024-2025'
            $table->integer('term'); // 1, 2, or 3
            $table->decimal('marks', 5, 2); // Marks obtained (e.g., 85.50)
            $table->decimal('total_marks', 5, 2)->default(100.00); // Total marks (default 100)
            $table->decimal('percentage', 5, 2)->nullable(); // Calculated percentage
            $table->string('grade', 5)->nullable(); // Grade (A+, A, B+, etc.)
            $table->text('remarks')->nullable(); // Teacher remarks
            $table->foreignId('entered_by')->nullable()->constrained('users', 'id')->onDelete('set null'); // Who entered the marks
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['student_id', 'subject_id', 'academic_year', 'term']);
            $table->index('grade_level');
            $table->index('academic_year');
            $table->index('term');

            // Unique constraint to prevent duplicate entries
            $table->unique(['student_id', 'subject_id', 'academic_year', 'term'], 'unique_student_subject_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
