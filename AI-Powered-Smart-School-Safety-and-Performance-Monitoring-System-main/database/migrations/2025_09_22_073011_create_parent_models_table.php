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
        Schema::create('parents', function (Blueprint $table) {
            $table->id('parent_id');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->string('parent_code', 20)->unique();
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['M', 'F', 'Other']);
            $table->string('nationality', 50)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('workplace', 150)->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->enum('relationship_type', ['Father', 'Mother', 'Guardian', 'Step-Father', 'Step-Mother', 'Other'])->default('Guardian');
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('address_line1', 200)->nullable();
            $table->string('address_line2', 200)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('home_phone', 20)->nullable();
            $table->string('mobile_phone', 20)->nullable();
            $table->string('work_phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('relationship_type');
            $table->index('is_emergency_contact');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
