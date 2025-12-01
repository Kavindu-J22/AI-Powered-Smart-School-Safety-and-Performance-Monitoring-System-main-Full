<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if settings already exist
        if (Setting::count() === 0) {
            Setting::create([
                'title' => env('APP_NAME', 'Laravel'),
                'logo' => null,
                'company_email' => 'info@smartschool.edu',
                'company_phone' => '+1234567890',
                'company_address' => '123 Education Street, Learning City',
                'mail_signature' => 'Best Regards,\nSmart School Management Team',
                'date_format' => 'd-m-Y',
                'time_format' => 'H:i:s',
                'timezone' => 'Asia/Colombo',
                'country' => 'LK',
                'copyright_text' => '© 2025 Smart School Management System. All rights reserved.',

                // Theme colors (from migration)
                'primary_color' => '#06C167',
                'secondary_color' => '#10B981',
                'accent_color' => '#F0FDF4',
                'success_color' => '#10B981',
                'warning_color' => '#F59E0B',
                'danger_color' => '#EF4444',
                'info_color' => '#3B82F6',

                // School specific settings
                'school_name' => env('APP_NAME', 'Laravel'),
                'school_motto' => 'Excellence in Education',
                'school_type' => 'Combined',
                'established_year' => 2020,
                'principal_name' => 'Dr. John Smith',
                'vice_principal_name' => 'Ms. Jane Doe',
                'total_capacity' => 1000,
                'website_url' => 'https://smartschool.edu',
                'social_facebook' => 'https://facebook.com/smartschool',
                'social_twitter' => 'https://twitter.com/smartschool',
                'social_instagram' => 'https://instagram.com/smartschool',

                // Academic settings
                'academic_year_start' => 'January',
                'academic_year_end' => 'December',
                'school_start_time' => '08:00:00',
                'school_end_time' => '15:00:00',
                'working_days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),

                // Theme settings
                'theme_mode' => 'light',
                'enable_animations' => true,
                'sidebar_style' => 'modern',
                'navbar_style' => 'glass',
            ]);

            echo "Settings seeded successfully!\n";
        } else {
            echo "Settings already exist, skipping seeder.\n";
        }
    }
}
