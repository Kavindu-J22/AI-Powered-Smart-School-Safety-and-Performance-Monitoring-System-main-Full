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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('slot_code')->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('slot_name');
            $table->enum('slot_type', ['regular', 'break', 'additional'])->default('regular');
            $table->integer('duration_minutes')->default(30);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['start_time', 'end_time']);
            $table->index('slot_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
