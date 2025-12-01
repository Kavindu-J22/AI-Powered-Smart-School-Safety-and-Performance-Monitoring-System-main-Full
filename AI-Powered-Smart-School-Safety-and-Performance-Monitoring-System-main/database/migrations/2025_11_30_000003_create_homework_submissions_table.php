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
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id('submission_id');
            $table->foreignId('homework_id')->constrained('homework', 'homework_id')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->json('answers');
            $table->json('evaluation_results')->nullable();
            $table->decimal('marks_obtained', 5, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade', 5)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'submitted', 'graded', 'late'])->default('assigned');
            $table->boolean('is_late')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['homework_id', 'student_id']);
            $table->index(['student_id', 'status']);
            $table->index(['homework_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
    }
};

