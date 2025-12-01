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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id('teacher_id');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->string('teacher_code', 20)->unique();
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->date('date_of_birth');
            $table->enum('gender', ['M', 'F', 'Other']);
            $table->string('nationality', 50)->nullable();
            $table->string('qualification', 200)->nullable();
            $table->string('specialization', 100)->nullable();
            $table->decimal('experience_years', 3, 1)->default(0);
            $table->date('joining_date');
            $table->string('employee_id', 20)->unique()->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_class_teacher')->default(false);
            $table->string('address_line1', 200)->nullable();
            $table->string('address_line2', 200)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('home_phone', 20)->nullable();
            $table->string('mobile_phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('specialization');
            $table->index('is_active');
            $table->index('is_class_teacher');
            $table->index('joining_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
