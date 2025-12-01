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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code')->unique();
            $table->string('subject_name');
            $table->string('grade_level');
            $table->text('description')->nullable();
            $table->integer('credits')->default(1);
            $table->enum('type', [
                'Core',
                'Elective',
                'Extracurricular',
                'Arts Stream',
                'Commerce Stream',
                'Science Stream',
                'Technology Stream'
            ])->default('Core');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['grade_level', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
