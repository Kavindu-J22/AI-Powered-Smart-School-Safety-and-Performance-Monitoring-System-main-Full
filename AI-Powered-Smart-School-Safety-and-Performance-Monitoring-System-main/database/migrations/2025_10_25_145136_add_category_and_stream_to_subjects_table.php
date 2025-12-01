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
        Schema::table('subjects', function (Blueprint $table) {
            // Add category field for subject categorization
            $table->enum('category', [
                'Core',
                'First Language',
                'Religion',
                'Aesthetic Studies',
                'Elective'
            ])->default('Core')->after('type');

            // Add is_required field for mandatory subjects
            $table->boolean('is_required')->default(false)->after('category');

            // Add stream field for Advanced Level subjects
            $table->enum('stream', [
                'Arts',
                'Commerce',
                'Science',
                'Technology'
            ])->nullable()->after('is_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['category', 'is_required', 'stream']);
        });
    }
};
