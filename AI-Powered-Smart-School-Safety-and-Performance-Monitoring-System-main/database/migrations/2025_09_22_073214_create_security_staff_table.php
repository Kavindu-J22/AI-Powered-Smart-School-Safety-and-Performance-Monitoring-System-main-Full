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
        Schema::create('security_staff', function (Blueprint $table) {
            $table->id('security_id');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->string('security_code', 20)->unique();
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->date('date_of_birth');
            $table->enum('gender', ['M', 'F', 'Other']);
            $table->string('nationality', 50)->nullable();
            $table->date('joining_date');
            $table->string('employee_id', 20)->unique()->nullable();
            $table->enum('shift', ['Morning', 'Afternoon', 'Evening', 'Night'])->default('Morning');
            $table->string('position', 100)->default('Security Guard');
            $table->string('photo_path', 500)->nullable();
            $table->boolean('is_active')->default(true);
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
            $table->index('shift');
            $table->index('position');
            $table->index('is_active');
            $table->index('joining_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_staff');
    }
};
