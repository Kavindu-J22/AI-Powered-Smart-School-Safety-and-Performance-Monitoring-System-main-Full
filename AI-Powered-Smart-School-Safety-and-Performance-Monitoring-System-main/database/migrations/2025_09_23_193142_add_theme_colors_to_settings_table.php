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
        Schema::table('settings', function (Blueprint $table) {
            // Theme colors
            $table->string('primary_color')->default('#06C167');
            $table->string('secondary_color')->default('#10B981');
            $table->string('accent_color')->default('#F0FDF4');
            $table->string('success_color')->default('#10B981');
            $table->string('warning_color')->default('#F59E0B');
            $table->string('danger_color')->default('#EF4444');
            $table->string('info_color')->default('#3B82F6');

            // School specific settings
            $table->string('school_name')->nullable();
            $table->string('school_motto')->nullable();
            $table->string('school_type')->nullable(); // Primary, Secondary, Combined
            $table->year('established_year')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('vice_principal_name')->nullable();
            $table->integer('total_capacity')->nullable();
            $table->string('website_url')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_instagram')->nullable();

            // Academic settings
            $table->string('academic_year_start')->default('January');
            $table->string('academic_year_end')->default('December');
            $table->time('school_start_time')->default('08:00:00');
            $table->time('school_end_time')->default('15:00:00');
            $table->json('working_days')->nullable();

            // Theme settings
            $table->enum('theme_mode', ['light', 'dark', 'auto'])->default('light');
            $table->boolean('enable_animations')->default(true);
            $table->string('sidebar_style')->default('modern'); // modern, classic, minimal
            $table->string('navbar_style')->default('glass'); // glass, solid, gradient
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color',
                'secondary_color',
                'accent_color',
                'success_color',
                'warning_color',
                'danger_color',
                'info_color',
                'school_name',
                'school_motto',
                'school_type',
                'established_year',
                'principal_name',
                'vice_principal_name',
                'total_capacity',
                'website_url',
                'social_facebook',
                'social_twitter',
                'social_instagram',
                'academic_year_start',
                'academic_year_end',
                'school_start_time',
                'school_end_time',
                'working_days',
                'theme_mode',
                'enable_animations',
                'sidebar_style',
                'navbar_style',
            ]);
        });
    }
};
