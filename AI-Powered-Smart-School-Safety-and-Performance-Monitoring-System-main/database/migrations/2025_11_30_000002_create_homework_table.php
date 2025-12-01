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
        Schema::create('homework', function (Blueprint $table) {
            $table->id('homework_id');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons', 'lesson_id')->onDelete('set null');
            $table->foreignId('subject_id')->constrained('subjects', 'id')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('school_classes', 'id')->onDelete('set null');
            $table->foreignId('assigned_by')->constrained('teachers', 'teacher_id')->onDelete('cascade');
            $table->integer('grade_level');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('questions');
            $table->integer('total_marks');
            $table->date('assigned_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'scheduled', 'active', 'completed', 'archived'])->default('draft');
            $table->integer('week_number')->nullable();
            $table->string('academic_year', 9)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['subject_id', 'grade_level', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};

