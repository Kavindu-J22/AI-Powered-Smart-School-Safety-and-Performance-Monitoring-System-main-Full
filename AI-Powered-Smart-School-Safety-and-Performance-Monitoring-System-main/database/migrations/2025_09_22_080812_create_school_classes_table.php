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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_code')->unique();
            $table->string('class_name');
            $table->integer('grade_level');
            $table->string('academic_year');
            $table->string('section')->nullable();
            $table->unsignedBigInteger('class_teacher_id')->nullable();
            $table->string('room_number')->nullable();
            $table->integer('capacity')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('class_teacher_id')->references('teacher_id')->on('teachers')->onDelete('set null');
            $table->index(['grade_level', 'class_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
