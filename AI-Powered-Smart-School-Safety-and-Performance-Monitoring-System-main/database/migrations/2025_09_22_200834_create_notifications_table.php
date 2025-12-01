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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'created', 'updated', 'deleted'
            $table->string('title'); // "Student Created", "Teacher Updated", etc.
            $table->text('message'); // Detailed message
            $table->string('entity_type'); // 'Student', 'Teacher', 'Subject', etc.
            $table->unsignedBigInteger('entity_id'); // ID of the affected entity
            $table->unsignedBigInteger('user_id'); // User who performed the action
            $table->string('user_name'); // Name of the user (cached for performance)
            $table->boolean('is_read')->default(false);
            $table->json('data')->nullable(); // Additional data like old/new values
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['is_read', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
